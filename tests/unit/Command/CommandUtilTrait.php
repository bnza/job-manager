<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

trait CommandUtilTrait
{

    protected function executeCommandTester(Command $command, array $input = [], array $options = []): CommandTester
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

        return $this->executeCommandTester($mockCommand, $input, $options);
    }
}
