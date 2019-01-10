<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\Job\JobInterface;
use Bnza\JobManagerBundle\Job\AbstractTask;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AbstractTaskTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $mockDispatcher = $this->createMock(EventDispatcher::class);
        $mockJob = $this->createMock(JobInterface::class);

        $mockJob
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $mockTask = $this->getMockForAbstractClass(
            AbstractTask::class,
            [],
            '',
            false,
            true,
            true,
            ['configure', 'terminate', 'getSteps', 'next', 'mockCallable', 'getJob']
        );

        $mockTask->expects($this->once())
            ->method('configure');

        $mockTask->expects($this->once())
            ->method('terminate');

        $mockTask->expects($this->once())
            ->method('next');

        $mockTask->method('getJob')
            ->willReturn($mockJob);

        $mockTask->expects($this->once())
            ->method('mockCallable')
            ->with(
                $this->equalTo('arg0'),
                $this->equalTo('arg1')
            );

        $mockTask
            ->method('getSteps')
            ->willReturn([
                [
                    [$mockTask, 'mockCallable'],
                    ['arg0', 'arg1'],
                ],
            ]);

        $mockTask->run();
    }

    public function testConstructor()
    {
        $num = (int) rand(0, 100);
        $jobId = sha1(microtime());
        $mockOm = $this->createMock(ObjectManagerInterface::class);
        $mockJob = $this->createMock(JobInterface::class);
        $mockJob
            ->method('getId')
            ->willReturn($jobId);
        $mockTask = $this->getMockForAbstractClass(
            AbstractTask::class,
            [],
            '',
            false,
            true,
            true,
            ['getSteps', 'getName']
        );
        $mockTask
            ->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                [
                    [$mockTask, 'mockCallable'],
                    ['arg0', 'arg1'],
                ],
            ]);
        $mockTask
            ->method('getName')
            ->willReturn('Dummy task name');

        $reflectedClass = new \ReflectionClass(AbstractTask::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($mockTask, [$mockOm, $mockJob, $num]);
        $this->assertEquals($num, $mockTask->getNum());
    }
}
