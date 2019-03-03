<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Command;

use Bnza\JobManagerBundle\Command\AbstractJobListenerCommand;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Tests\Fixture\Runner\Job\DummyJob1;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;
use Bnza\JobManagerBundle\Tests\UtilsTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


class AbstractJobListenerCommandTest extends KernelTestCase
{
    use CommandUtilTrait;
    use MockUtilsTrait;
    use UtilsTrait;

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
    public function testListenedEventsWillTriggerMethods(string $method, int $count, string $class)
    {
        $id = sha1(microtime());
        $this->mockJobEntity[0] = new JobEntity($id);
        $mockOm = $this->getMockObjectManager();
        $mockOm->method('find')->willReturn($this->mockJobEntity[0]);
        $this->mockDispatcher = new EventDispatcher();
        $mockJob = $this->getMockJob(DummyJob1::class);
        $this->invokeConstructor(DummyJob1::class, $mockJob, [$mockOm, $this->mockDispatcher, $this->mockJobEntity[0]]);

        $command = $this->getMockForTypeWithMethods(
            AbstractJobListenerCommand::class,
            [
                'onJobStarted',
                'onJobEnded',
                'onTaskStarted',
                'onTaskEnded',
                'onTaskStepStarted',
                'onTaskStepEnded'
            ]
        );

        $output = $this->getMockForAbstractClass(OutputInterface::class);
        $input = $this->getMockForAbstractClass(InputInterface::class);

        $command->expects($this->exactly($count))->method($method)->with($this->isInstanceOf($class));
        $this->invokeConstructor(AbstractJobListenerCommand::class, $command, [$mockOm, $this->mockDispatcher]);
        $command->initialize($input, $output);
        $mockJob->run();
    }
}
