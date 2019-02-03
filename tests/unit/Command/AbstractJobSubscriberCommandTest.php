<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Command;

use Bnza\JobManagerBundle\Command\AbstractJobSubscriberCommand;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Tests\Fixture\Runner\Job\DummyJob1;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;
use Bnza\JobManagerBundle\Tests\UtilsTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class AbstractJobSubscriberCommandTest extends KernelTestCase
{
    use CommandUtilTrait;
    use MockUtilsTrait;
    use UtilsTrait;

    public function testMethodGetDispatcherWillReturnDispatcher()
    {
        $this->getMockDispatcher();
        $this->getMockObjectManager();
        $command = $this->getMockForTypeWithMethods(AbstractJobSubscriberCommand::class, []);
        $this->invokeConstructor(AbstractJobSubscriberCommand::class, $command, [$this->mockOm, $this->mockDispatcher]);
        $getDispatcher = $this->getNonPublicMethod($command, 'getDispatcher');


        $this->assertInstanceOf(EventDispatcherInterface::class, $getDispatcher->invoke($command));
    }

    /**
     * @testWith    ["onJobStarted", 1, "\\Bnza\\JobManagerBundle\\Event\\JobStartedEvent"]
     *              ["onJobEnded", 1, "\\Bnza\\JobManagerBundle\\Event\\JobEndedEvent"]
     *              ["onTaskStarted", 2, "\\Bnza\\JobManagerBundle\\Event\\TaskStartedEvent"]
     *              ["onTaskEnded", 2, "\\Bnza\\JobManagerBundle\\Event\\TaskEndedEvent"]
     *              ["onTaskStepStarted", 4, "\\Bnza\\JobManagerBundle\\Event\\TaskStepStartedEvent"]
     *              ["onTaskStepEnded", 4, "\\Bnza\\JobManagerBundle\\Event\\TaskStepEndedEvent"]
     *
     * @param string $method
     * @param int $count
     * @param string $class
     */
    public function testSubscribedEventsWillTriggerMethods(string $method, int $count, string $class)
    {
        $id = sha1(microtime());
        $this->mockJobEntity[0] = new JobEntity($id);
        $mockOm = $this->getMockObjectManager();
        $mockOm->method('find')->willReturn($this->mockJobEntity[0]);
        $this->mockDispatcher = new EventDispatcher();
        $mockJob = $this->getMockJob(DummyJob1::class);
        $this->invokeConstructor(DummyJob1::class, $mockJob, [$mockOm, $this->mockDispatcher, $this->mockJobEntity[0]]);

        $command = $this->getMockForTypeWithMethods(
            AbstractJobSubscriberCommand::class,
            [
                'onJobStarted',
                'onJobEnded',
                'onTaskStarted',
                'onTaskEnded',
                'onTaskStepStarted',
                'onTaskStepEnded'
            ]
        );

        $this->mockDispatcher->addSubscriber($command);

        $command->expects($this->exactly($count))->method($method)->with($this->isInstanceOf($class));
        $this->invokeConstructor(AbstractJobSubscriberCommand::class, $command, [$mockOm, $this->mockDispatcher]);
        $mockJob->run();
    }

    /**
     * @testWith    ["displayJobHeader", 1, "\\Bnza\\JobManagerBundle\\Info\\JobInfoInterface"]
     *              ["updateStatusDisplay", 1, "\\Bnza\\JobManagerBundle\\Info\\JobInfoInterface"]
     *              ["updateOverallProgress", 1, "\\Bnza\\JobManagerBundle\\Info\\JobInfoInterface"]
     *              ["updateTaskProgress", 6, "\\Bnza\\JobManagerBundle\\Info\\TaskInfoInterface"]
     *              ["setJobComplete", 1, "\\Bnza\\JobManagerBundle\\Info\\JobInfoInterface"]
     *              ["setTaskComplete", 2, "\\Bnza\\JobManagerBundle\\Info\\TaskInfoInterface"]
     *
     * @param string $method
     * @param int $count
     * @param string $class
     */
    public function testSubscribedMethodsWillCallCommandMethods(string $method, int $count, string $class)
    {
        $id = sha1(microtime());
        $this->mockJobEntity[0] = new JobEntity($id);
        $mockOm = $this->getMockObjectManager();
        $mockOm->method('find')->willReturn($this->mockJobEntity[0]);
        $this->mockDispatcher = new EventDispatcher();
        $mockJob = $this->getMockJob(DummyJob1::class);
        $this->invokeConstructor(DummyJob1::class, $mockJob, [$mockOm, $this->mockDispatcher, $this->mockJobEntity[0]]);

        $command = $this->getMockForTypeWithMethods(
            AbstractJobSubscriberCommand::class,
            [
                'displayJobHeader',
                'updateStatusDisplay',
                'updateTaskProgress',
                'updateOverallProgress',
                'setJobComplete',
                'setTaskComplete',
            ]
        );

        $this->mockDispatcher->addSubscriber($command);

        $command->expects($this->exactly($count))->method($method)->with($this->isInstanceOf($class));
        $this->invokeConstructor(AbstractJobSubscriberCommand::class, $command, [$mockOm, $this->mockDispatcher]);
        $mockJob->run();
    }
}
