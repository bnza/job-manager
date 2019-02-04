<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */


namespace Bnza\JobManagerBundle\Tests;

use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Event\SummaryEntryEvent;
use Bnza\JobManagerBundle\Event\TaskStartedEvent;
use Bnza\JobManagerBundle\Info\InfoInterface;
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\Summary\Entry\AbstractInfoEntry;
use Bnza\JobManagerBundle\Summary\Entry\ErrorEntry;
use Bnza\JobManagerBundle\Summary\Summary;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SummaryTest extends \PHPUnit\Framework\TestCase
{
    use MockUtilsTrait;
    use UtilsTrait;

    /**
     * @var Summary
     */
    private $summary;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function tearDown()
    {
        $this->tearDownTestDirectories();
    }

    private function setUpMockJob()
    {
        $this->getMockJob();
        $this->mockJob->method('getId')->willReturn(sha1(microtime()));
        $this->mockJob->method('getDispatcher')->willReturn($this->dispatcher);
    }

    private function setUpMockTask()
    {
        if (!$this->mockJob instanceof JobInterface) {
            $this->setUpMockJob();
        }
        $mockTask = $this->getMockTask();
        $mockTask->method('getJob')->willReturn($this->mockJob);
        $mockTask->method('getNum')->willReturn((int) mt_rand(0, 100));
    }

    public function setUp()
    {
        $this->setUpTestDirectories();
        $this->summary = new Summary($this->originDir);
        $this->dispatcher = new EventDispatcher();
        $this->setUpMockJob();
    }

    public function assertPreConditions()
    {
        $this->assertTestDirectoriesAreEmpty();
        $this->assertEmpty($this->summary->getEntries());
    }

    public function testMethodOnJobStartedWillSetANewJobEntries()
    {
        $event = new JobStartedEvent($this->mockJob);
        $this->summary->onJobStarted($event);
        $this->assertArrayHasKey($this->mockJob->getId(), $this->summary->getEntries());
    }

    public function testMethodOnJobEndedWillCreateAJsonReport()
    {
        \mkdir($this->originDir.DIRECTORY_SEPARATOR.$this->mockJob->getId());
        $event = new JobEndedEvent($this->mockJob);
        $this->summary->onJobEnded($event);
        $this->assertFileExists($this->originDir.DIRECTORY_SEPARATOR.$this->mockJob->getId().DIRECTORY_SEPARATOR.'summary.json');
    }

    public function testMethodOnTaskStartedWillSetANewTaskEntry()
    {
        $this->setUpMockTask();
        $event = new JobStartedEvent($this->mockJob);
        $this->summary->onJobStarted($event);
        $event = new TaskStartedEvent($this->mockTasks[0]);
        $this->summary->onTaskStarted($event);
        $entries = $this->summary->getEntries();
        $this->assertArrayHasKey($this->mockTasks[0]->getNum(), $entries[$this->mockJob->getId()]['tasks']);
    }

    public function testMethodOnTaskStartedNoJobBeforeWillSetANewTaskEntry()
    {
        $this->setUpMockTask();
        $event = new TaskStartedEvent($this->mockTasks[0]);
        $this->summary->onTaskStarted($event);
        $entries = $this->summary->getEntries();
        $this->assertArrayHasKey($this->mockTasks[0]->getNum(), $entries[$this->mockJob->getId()]['tasks']);
    }

    /**
     * @dataProvider addLogDataProvider
     * @param string $class
     * @param $runnable
     * @param array $expected
     */
    public function testMethodAddLogWillSetANewJobEntries(string $entryClass, InfoInterface $runnable, array $expected)
    {
        $entry = new $entryClass($runnable, 'A message', ['key'=>'value']);
        $event = new SummaryEntryEvent($entry);
        $this->summary->addLog($event);
        $entries = $this->summary->getEntries();
        if ($runnable instanceof JobInterface) {
            $logs = $entries[$runnable->getId()]['log'];
        } else {
            $logs = $entries[$runnable->getJob()->getId()]['tasks'][$runnable->getNum()]['log'];
        }

        $this->assertCount(1, $logs);
        $this->assertEquals($expected, $logs[0]);
    }

    /**
     * @dataProvider eventSubscriberDataProvider
     * @param $method
     * @param string $eventName
     * @param Event $event
     */
    public function testMethodGetSubscribedEventsWillCallExpectedMethods($method, string $eventName, Event $event, string $eventClass)
    {
        $mock = $this->getMockForTypeWithMethods(
            Summary::class,
            [
                'onJobStarted',
                'onJobEnded',
                'onTaskStarted',
                'addLog'
            ]
        );
        $mock->expects($this->once())->method($method)->with($this->isInstanceOf($eventClass));
        $this->dispatcher->addSubscriber($mock);
        $this->dispatcher->dispatch($eventName, $event);
    }

    /**
     * @testWith    [true]
     *              [false]
     */
    public function testMethodGetLogEntriesWillReturnExpectedValues(bool $isJob)
    {
        $this->setUpMockTask();
        $expected1 = [
            'level' => 'warning',
            'message' => 'Job',
            'context' => [
                'key'=>'value'
            ]
        ];

        $expected2 = [
            'level' => 'warning',
            'message' => 'Task',
            'context' => [
                'key'=>'value'
            ]
        ];

        $entries = [
            $this->mockJob->getId() => [
                'tasks' => [
                    $this->mockTasks[0]->getNum() => [
                        'log' => [
                            $expected2
                        ]
                    ]
                ],
                'log' => [
                    $expected1
                ]
            ]
        ];

        $this->summary = $this->getMockForTypeWithMethods(Summary::class, ['getEntries']);
        $this->summary->expects($this->once())->method('getEntries')->willReturn($entries);

        $expected = [
          $isJob ? $expected1 : $expected2
        ];
        $actual = $isJob
            ? $this->summary->getLogEntries($this->mockJob->getId())
            : $this->summary->getLogEntries($this->mockJob->getId(), $this->mockTasks[0]->getNum());
        $this->assertEquals($expected, $actual);
    }

    public function addLogDataProvider()
    {
        $this->setUpMockTask();
        return [
            [
                ErrorEntry::class,
                $this->mockTasks[0],
                [
                    'level' => 'error',
                    'message' => 'A message',
                    'context' => [
                        'key'=>'value'
                    ]
                ]
            ],
            [
                ErrorEntry::class,
                $this->mockJob,
                [
                    'level' => 'error',
                    'message' => 'A message',
                    'context' => [
                        'key'=>'value'
                    ]
                ]
            ],

        ];
    }

    public function eventSubscriberDataProvider()
    {
        $this->setUpMockTask();
        return [
            [
                'onJobStarted',
                JobStartedEvent::NAME,
                new JobStartedEvent($this->mockJob),
                JobStartedEvent::class
            ],
            [
                'onJobEnded',
                JobEndedEvent::NAME,
                new JobEndedEvent($this->mockJob),
                JobEndedEvent::class
            ],
            [
                'onTaskStarted',
                TaskStartedEvent::NAME,
                new TaskStartedEvent($this->mockTasks[0]),
                TaskStartedEvent::class
            ],
            [
                'addLog',
                SummaryEntryEvent::NAME,
                new SummaryEntryEvent($this->getMockForTypeWithMethods(AbstractInfoEntry::class, [])),
                SummaryEntryEvent::class
            ],
        ];
    }

}
