<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Task;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use PHPUnit\Framework\MockObject\MockBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\Runner\Task\TaskInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait MockJobUtilsTrait
{
    abstract public function getMockBuilder($className): MockBuilder;

    abstract public function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false): MockObject;

    public function getMockJob(
        string $id = '',
        string $class = JobInterface::class,
        array $methods = []
    ): JobInterface {
        $reflectionClass = new \ReflectionClass($class);
        $isInstantiable = $reflectionClass->isInstantiable();

        $id = $id ?: sha1(microtime());
        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $methods = \array_merge(['getId', 'getDispatcher', 'getName'], $methods);

        if ($isInstantiable) {
            return $mockDispatcher;
        } else {
            $mockJob = $this->getMockForAbstractClass(
                $class,
                [],
                '',
                false,
                false,
                true,
                $methods
            );
        }

        $mockJob->method('getId')->willReturn($id);
        $mockJob->method('getName')->willReturn('Dummy job name '. $id);
        $mockJob->method('getDispatcher')->willReturn($mockDispatcher);

        return $mockJob;
    }

    public function getMockTask(string $class, array $methods = [], JobInterface $job = null): TaskInterface
    {
        if (!$job) {
            $job = $this->getMockJob();
        }

        $methods = \array_merge(['getJob'], $methods);
        $mockTask = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods($methods)
            ->getMock();

        $mockTask->method('getJob')->willReturn($job);

        return $mockTask;
    }

    public function getMockTaskAndInvokeConstructor(string $class, $specificArgs = [], array $baseArgs = [],  array $methods = []): TaskInterface
    {
        $mockTask = $this->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods($methods)
            ->getMock();

        $reflectedClass = new \ReflectionClass($class);
        $constructor = $reflectedClass->getConstructor();

        if (!isset($baseArgs[0])) {
            $baseArgs[0] = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        }
        if (!isset($baseArgs[1])) {
            $baseArgs[1] = $this->getMockJob();
        }
        if (!isset($baseArgs[2])) {
            $baseArgs[2] = 1;
        }

        $constructor->invokeArgs($mockTask, \array_merge($baseArgs, $specificArgs));

        return $mockTask;
    }
}
