<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Job\AbstractTask;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

class AbstractTaskTest extends \PHPUnit\Framework\TestCase
{
    public function testRun()
    {
        $mockEntity = $this->createMock(JobEntityInterface::class);

        $mockTask = $this->getMockForAbstractClass(
            AbstractTask::class,
            [],
            '',
            false,
            true,
            true,
            ['configure', 'terminate', 'getSteps', 'next', 'mockCallable']
        );

        $mockTask->expects($this->once())
            ->method('configure');

        $mockTask->expects($this->once())
            ->method('terminate');

        $mockTask->expects($this->once())
            ->method('next');

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
        $mockOm = $this->createMock(ObjectManagerInterface::class);
        $mockEntity = $this->createMock(JobEntityInterface::class);
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
        $constructor->invokeArgs($mockTask, [$mockOm, $mockEntity, $num]);
        $this->assertEquals($num, $mockTask->getNum());
    }
}
