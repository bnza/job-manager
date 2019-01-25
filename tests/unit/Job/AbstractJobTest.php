<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Job\AbstractJob;
use Bnza\JobManagerBundle\Task\AbstractTask;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Job\JobInterface;
use Bnza\JobManagerBundle\Job\Status;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

class DummyTask extends AbstractTask
{
    public $prop1;
    public $prop2;

    public function __construct(ObjectManagerInterface $om, JobInterface $job, int $num, string $param1, int $param2 = 2)
    {
        parent::__construct($om, $job, $num);
        $this->prop1 = $param1;
        $this->prop2 = $param2;
    }

    public function getName(): string
    {
        return 'DummyTask name';
    }

    public function getSteps(): iterable
    {
        return [
            [$this, 'runMethod'], ['arg0', 'arg1'],
        ];
    }

    public function run(): void
    {
    }
}

class DummyNonTask
{
}

class AbstractJobTest extends \PHPUnit\Framework\TestCase
{
    public function initAbstractJobMockConstructor(
        Status $mockStatus = null,
        JobEntityInterface $mockEntity = null,
        ObjectManagerInterface $mockOm = null,
        EventDispatcher $mockDispatcher = null,
        AbstractTask $mockTask = null,
        AbstractJob $mockJob = null
    ) {
        if (!$mockStatus) {
            $mockStatus = $this->createMock(Status::class);
        }

        if (!$mockEntity) {
            $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        if (!$mockOm) {
            $mockOm = $this->createMock(ObjectManagerInterface::class);
        }

        if (!$mockOm) {
            $mockOm = $this->createMock(ObjectManagerInterface::class);
        }

        if (!$mockDispatcher) {
            $mockDispatcher = $this->createMock(EventDispatcher::class);
        }

        if (!$mockTask) {
            $mockTask = $this->createMock(AbstractTask::class);
        }

        if (!$mockJob) {
            $mockJob = $this->getMockForAbstractClass(
                AbstractJob::class,
                [],
                '',
                false,
                true,
                true,
                ['getName', 'getSteps', 'initTask']
            );
        }

        $reflectedClass = new \ReflectionClass(AbstractJob::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($mockJob, [$mockOm, $mockDispatcher, $mockEntity]);

        return \compact(
            'mockStatus',
            'mockEntity',
            'mockOm',
            'mockDispatcher',
            'mockTask',
            'mockJob'
        );
    }

    public function testConstructor()
    {
        $context = $this->initAbstractJobMockConstructor();

        /*
         * @var $mockJob AbstractJob
         * @var $mockDispatcher
         * @var $mockTask
         */
        \extract($context);

        $this->assertEquals($mockDispatcher, $mockJob->getDispatcher());

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2'],
            ]);

        $mockJob->method('initTask')
            ->with($this->onConsecutiveCalls([
                [0, ['TaskClass1']],
                [1, ['TaskClass2']],
            ]));

        $mockJob->run();

        return $context;
    }

    /**
     * @depends testConstructor
     */
    public function testGetParameters(array $context)
    {
        /**
         * @var AbstractJob
         */
        $mockJob = $context['mockJob'];
        $this->assertInstanceOf(ParameterBag::class, $mockJob->getParameters());
    }

    public function testRunCallMethods()
    {
        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['running', 'runTask', 'success', 'getSteps']
        );

        $mockJob->expects($this->once())
            ->method('running');

        $mockJob->expects($this->once())
            ->method('success');

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2'],
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('runTask')
            ->withConsecutive(
                [0, ['TaskClass1']],
                [1, ['TaskClass2']]
            );

        $mockJob->run();
    }

    public function initTaskDataProvider()
    {
        $arg2 = (int) rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                [DummyTask::class, $arg1],
                [$arg1],
            ],
            [
                [DummyTask::class, [$arg1, $arg2]],
                [$arg1, $arg2],
            ],
        ];
    }

    public function initMockJob(array $taskData, array $mockedMethods = [])
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $methods = array_merge(
            ['getObjectManager', 'getEntity', 'getSteps', 'success', 'running'],
            $mockedMethods
        );

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            $methods
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                $taskData,
            ]);

        $mockJob->expects($this->once())
            ->method('success');

        return $mockJob;
    }

    /**
     * @param array $taskData
     * @param array $expectedArgs
     *
     * @dataProvider initTaskDataProvider
     */
    public function testInitTask(array $taskData, array $expectedArgs)
    {
        $mockJob = $this->initMockJob($taskData, ['createTask']);

        $mockJob->expects($this->once())
            ->method('createTask')
            ->with(
                $this->equalTo(DummyTask::class),
                $this->equalTo(0),
                $this->equalTo($expectedArgs)
            );

        $mockJob->run();
    }

    /**
     * @param array $taskData
     * @param array $expectedArgs
     *
     * @dataProvider initTaskDataProvider
     */
    public function testCreateTask(array $taskData, array $expectedArgs)
    {
        $mockJob = $this->initMockJob($taskData);
        $mockJob->run();
        $task = $mockJob->getTask(0);
        $this->assertEquals($expectedArgs[0], $task->prop1);
        $this->assertEquals(isset($expectedArgs[1]) ? $expectedArgs[1] : 2, $task->prop2);
    }

    public function testInitTaskException()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getDispatcher', 'error']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                [DummyNonTask::class],
            ]);

        $mockJob->expects($this->once())
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $mockJob->expects($this->once())
            ->method('error')
            ->with($this->isInstanceOf(\InvalidArgumentException::class));

        $mockJob->run();
    }

    public function testRollback()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockTask = $this->createMock(AbstractTask::class);

        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getTasks', 'initTask', 'getDispatcher']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2'],
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('initTask')
            ->willReturn($mockTask);

        $mockJob->expects($this->once())
            ->method('getTasks')
            ->willReturn([$mockTask, $mockTask]);

        $mockJob->expects($this->any())
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $mockTask
            ->expects($this->exactly(2))
            ->method('run')
            ->will(
                $this->onConsecutiveCalls(
                    true,
                    $this->throwException(new \Exception('Dummy exception'))
                )
            );

        $mockTask
            ->expects($this->exactly(2))
            ->method('rollback');

        $mockJob->run();
    }
}
