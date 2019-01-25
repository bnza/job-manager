<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Command;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;
use Bnza\JobManagerBundle\Runnable\Job\JobInfo;
use Bnza\JobManagerBundle\Runnable\Status;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Command\JobInfoCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class JobInfoCommandTest extends KernelTestCase
{
    public function jobEntityStatusDataProvider()
    {
        return [
            [
                new Status(Status::RUNNING),
                'Running...',
            ],
            [
                new Status(Status::SUCCESS),
                'Success',
            ],
            [
                new Status(Status::ERROR),
                'Error',
            ],
        ];
    }

    public function getObjectManagerMock($entity): ObjectManagerInterface
    {
        $om = $this->createMock(ObjectManagerInterface::class);
        $om->method('find')->willReturn($entity);

        return $om;
    }

    /**
     * Command class "XXX" is not correctly initialized. You probably forgot to call the parent constructor.
     *
     * @param Command $command
     * @param array   $arguments
     *
     * @throws \ReflectionException
     */
    public function invokeCommandConstructor(Command $command, array $arguments = [])
    {
        $reflectedClass = new \ReflectionClass(Command::class);
        $reflectedClass->getConstructor()->invokeArgs($command, $arguments);
    }

    public function executeCommandTesterOnMock(Command $mockCommand, array $input = [], array $options = []): CommandTester
    {
        $this->invokeCommandConstructor($mockCommand);
        $defaultInput = [
            'command' => $mockCommand->getName(),
        ];
        // Force ConsoleOutput
        $defaultOptions = [
            'capture_stderr_separately' => true,
        ];

        $input = \array_merge($input, $defaultInput);
        $options = \array_merge($options, $defaultOptions);

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->add($mockCommand);

        $command = $application->find($mockCommand->getName());
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    public function executeCommandTester(Command $command, array $input = [], array $options = []): CommandTester
    {
        $defaultInput = [
            'command' => $command->getName(),
        ];
        // Force ConsoleOutput
        $defaultOptions = [
            'capture_stderr_separately' => true,
        ];

        $input = \array_merge($input, $defaultInput);
        $options = \array_merge($options, $defaultOptions);

        $kernel = static::createKernel();
        $application = new Application($kernel);
        $application->add($command);

        $appCommand = $application->find($command->getName());
        $commandTester = new CommandTester($appCommand);
        $commandTester->execute($input, $options);

        return $commandTester;
    }

    public function testJobNotFoundException()
    {
        $id = sha1(microtime());
        $om = $this->createMock(ObjectManagerInterface::class);
        $om->method('find')->willThrowException(new JobManagerEntityNotFoundException($id));

        $jobCommand = new JobInfoCommand($om);

        $commandTester = $this->executeCommandTester($jobCommand, ['job_id' => $id]);
        $this->assertTrue((bool) $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertContains('No job with ID '.$id.' found', $output);
    }

    public function testDisplayJobHeader()
    {
        $id = sha1(microtime());
        $entity = $this->createMock(JobEntityInterface::class);
        $entity->method('getStatus')->willReturn(new Status(Status::SUCCESS));
        $entity->method('getId')->willReturn($id);
        $entity->method('getName')->willReturn(JobInfoCommand::getDefaultName());
        $om = $this->getObjectManagerMock($entity);

        $jobCommand = new JobInfoCommand($om);

        $commandTester = $this->executeCommandTester($jobCommand, ['job_id' => $id]);
        $output = $commandTester->getDisplay();
        $this->assertContains('Runnable: '.$id, $output);
        $this->assertContains('Name: '.JobInfoCommand::getDefaultName(), $output);
    }

    /**
     * @dataProvider jobEntityStatusDataProvider
     *
     * @param Status $status
     * @param string $display
     */
    public function testUpdateDisplayStatus(Status $status, string $display)
    {
        $id = sha1(microtime());
        $entity = $this->createMock(JobEntityInterface::class);
        $entity->method('getStatus')->willReturn($status);
        $entity->method('getId')->willReturn($id);

        $om = $this->getObjectManagerMock($entity);

        $jobCommand = new JobInfoCommand($om);

        $commandTester = $this->executeCommandTester($jobCommand, ['job_id' => $id]);
        $output = $commandTester->getDisplay();
        $this->assertContains($display, $output);
    }

    public function testUpdateOverallProgress()
    {
        $id = sha1(microtime());
        $entity = $this->createMock(JobEntityInterface::class);
        $entity->method('getStatus')->willReturn(new Status(Status::RUNNING));
        $entity->method('getId')->willReturn($id);
        $entity->method('getName')->willReturn(JobInfoCommand::getDefaultName());
        $entity->method('getStepsNum')->willReturn(3);
        $entity->method('getCurrentStepNum')->willReturn(2);
        $taskEntity = $this->createMock(TaskEntityInterface::class);
        $taskEntity->method('getName')->willReturn('Dummy task');
        $entity->method('getTask')->willReturn($taskEntity);
        $om = $this->getObjectManagerMock($entity);

        $jobCommand = new JobInfoCommand($om);

        $commandTester = $this->executeCommandTester($jobCommand, ['job_id' => $id]);
        $output = $commandTester->getDisplay();
        $this->assertContains(' 2/3: Dummy task', $output);
    }

    public function getMockJobInfoCommandForFollow(): JobInfoCommand
    {
        $info = $this->createMock(JobInfo::class);
        $info
            ->method('isRunning')
            ->will($this->onConsecutiveCalls(
                true,
                true,
                false
            ));

        // Configure mock command
        $mockCommand = $this->createPartialMock(
            JobInfoCommand::class,
            [
                'getInfo',
                'getName',
                'updateDisplay',
            ]
        );
        $mockCommand->method('getName')->willReturn(JobInfoCommand::getDefaultName());
        $mockCommand
            ->expects($this->once())
            ->method('getInfo')
            ->willReturn($info);

        $mockCommand
            ->expects($this->exactly(3))
            ->method('updateDisplay')
            ->willReturn($info);

        return $mockCommand;
    }

    public function testFollowOption()
    {
        $id = sha1(microtime());
        $mockCommand = $this->getMockJobInfoCommandForFollow();
        $this->executeCommandTesterOnMock($mockCommand, ['job_id' => $id, '--follow' => true]);
    }

    public function testFollowIntervalDefault()
    {
        $id = sha1(microtime());
        $mockCommand = $this->getMockJobInfoCommandForFollow();
        $start = microtime(true);
        $this->executeCommandTesterOnMock($mockCommand, ['job_id' => $id, '--follow' => true]);
        $stop = microtime(true);
        $this->assertEquals(JobInfoCommand::DEFAULT_INTERVAL*2/1000, $stop-$start, '', 0.1);
    }

    public function testFollowInterval()
    {
        $interval = 100;
        $id = sha1(microtime());
        $mockCommand = $this->getMockJobInfoCommandForFollow();
        $start = microtime(true);
        $this->executeCommandTesterOnMock($mockCommand, ['job_id' => $id, '--follow' => true, '--interval' => $interval]);
        $stop = microtime(true);
        $this->assertEquals($interval*2/1000, $stop-$start, '', 0.1);
    }
}
