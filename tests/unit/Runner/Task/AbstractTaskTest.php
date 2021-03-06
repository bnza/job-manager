<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Task;

use Bnza\JobManagerBundle\Event\TaskStepStartedEvent;
use Bnza\JobManagerBundle\Event\TaskStepEndedEvent;
use Bnza\JobManagerBundle\Runner\Task\AbstractTask;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractTaskTest extends \PHPUnit\Framework\TestCase
{
    use MockUtilsTrait;


    public function testMethodRunWillCallExpectedMethods()
    {

        $this->getMockDispatcher();
        $this->getMockJob();
        $mockTaskEntity = $this->getMockTaskEntity();

        $this->mockJob
            ->method('getDispatcher')
            ->willReturn($this->mockDispatcher);

        $mockTask = $this->getMockTask(
            AbstractTask::class,
            ['configure', 'terminate', 'getSteps', 'next', 'executeStep', 'getJob', 'isCancelled', 'getEntity', 'getStepsNum', 'persist']
        );

        $mockTask->expects($this->once())
            ->method('configure');

        $mockTask->expects($this->once())
            ->method('terminate');

        $mockTask->expects($this->once())
            ->method('next');

        $mockTask->method('getJob')
            ->willReturn($this->mockJob);

        $mockTask->expects($this->once())
            ->method('executeStep')
            ->with(
                $this->equalTo(['arg0', 'arg1'])
            );

        $mockTask->expects($this->once())
            ->method('getEntity')
            ->willReturn($mockTaskEntity);

        $mockTask->expects($this->once())
            ->method('getStepsNum');

        $mockTask
            ->method('getSteps')
            ->willReturn([['arg0', 'arg1']]);

        $mockTask->run();
    }

    public function testMethodSetStepIntervalWillSetStepInterval()
    {
        $interval = (int) mt_rand(0, 100);
        $mockTask = $this->getMockTask(AbstractTask::class);
        $this->assertEquals(1, $mockTask->getStepInterval());
        $mockTask->setStepInterval($interval);
        $this->assertEquals($interval, $mockTask->getStepInterval());
    }

    /**
     * @param int $interval
     * @param int $expected
     * @testWith    [0,0]
     *              [1,33]
     *              [3,11]
     *              [10,4]
     *              [11,3]
     */
    public function testMethodRunWillDispatchDependingOnStepInterval(int $interval, int $expected)
    {
        $this->getMockDispatcher(EventDispatcher::class, ['dispatch']);

        $this->mockDispatcher
            ->expects($spy = $this->any())
            ->method('dispatch');

        $this->getMockJob();

        $this->mockJob
            ->method('getDispatcher')
            ->willReturn($this->mockDispatcher);

        $mockTask = $this->getMockTask(
            AbstractTask::class,
            ['getSteps', 'next', 'executeStep', 'getJob', 'getStepInterval', 'isCancelled', 'setStepsNum']
        );

        $mockTask->method('getJob')
            ->willReturn($this->mockJob);

        $mockTask->method('getStepInterval')
            ->willReturn($interval);

        $generator = function ($num) {
            for ($i = 0; $i < $num; $i++) {
                yield [$i];
            }
        };

        $mockTask
            ->method('getSteps')
            ->willReturn($generator(33));

        $mockTask->run();

        $countStartEvents = 0;
        $countEndEvents = 0;
        $invocations = $spy->getInvocations();
        foreach ($invocations as $invocation) {
            $parameters = $invocation->getParameters();
            $event = $parameters[0];
            $eventName = $parameters[1];
            if ($eventName === TaskStepStartedEvent::NAME) {
                $this->assertInstanceOf(TaskStepStartedEvent::class, $event);
                ++$countStartEvents;
            } elseif ($eventName === TaskStepEndedEvent::NAME) {
                $this->assertInstanceOf(TaskStepEndedEvent::class, $event);
                ++$countEndEvents;
            }
        }
        $this->assertEquals($expected, $countStartEvents);
        $this->assertEquals($expected, $countEndEvents);
    }

    public function testConstructorWillSetTaskNum()
    {
        $num = (int)rand(0, 100);
        $jobId = sha1(microtime());

        $this->getMockObjectManager();
        $this->getMockJob();

        $this->mockJob
            ->method('getId')
            ->willReturn($jobId);

        $mockTask = $this->getMockTask(
            AbstractTask::class,
            ['getName', 'getClass']
        );

        $mockTask
            ->expects($this->once())
            ->method('getName');

        $mockTask
            ->expects($this->once())
            ->method('getClass');

        $mockTask
            ->method('getName')
            ->willReturn('Dummy task name');

        $this->invokeConstructor(AbstractTask::class, $mockTask, [$this->mockOm, $this->mockJob, $num]);

        $this->assertEquals($num, $mockTask->getNum());
    }
}
