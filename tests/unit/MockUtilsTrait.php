<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\MockBuilder;

trait MockUtilsTrait
{
    /**
     * @var MockObject|\Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected $mockDispatcher;

    /**
     * @var MockObject|\Bnza\JobManagerBundle\Runner\Job\JobInterface
     */
    protected $mockJob;

    /**
     * @var MockObject[]|\Bnza\JobManagerBundle\Runner\Task\TaskInterface[]
     */
    protected $mockTasks = [];

    /**
     * @var MockObject|\Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface
     */
    protected $mockOm;

    abstract public function getMockBuilder($className): MockBuilder;

    abstract protected function createMock($originalClassName): MockObject;

    abstract protected function getMockForAbstractClass($originalClassName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false): MockObject;

    abstract protected function getMockForTrait($traitName, array $arguments = [], $mockClassName = '', $callOriginalConstructor = true, $callOriginalClone = true, $callAutoload = true, $mockedMethods = [], $cloneArguments = false): MockObject;

    protected function getClassType(string $class): string
    {
        $rc = new \ReflectionClass($class);
        if ($rc->isInstantiable()) {
            return 'class';
        } else {
            if ($rc->isInterface()) {
                return 'interface';
            } else if ($rc->isAbstract()) {
                return 'abstract';
            } else if ($rc->isTrait()) {
                return 'trait';
            }
        }
    }

    protected function getMockWithMockedMethods(string $className, array $methods): MockObject
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods($methods)
            ->getMock();
    }

    protected function getMockForAbstractClassWithMockedMethods(string $className, array $methods): MockObject
    {
        return $this->getMockForAbstractClass(
            $className,
            [],
            '',
            false,
            true,
            true,
            $methods
        );
    }

    protected function getMockForTraitWithMockedMethods(string $className, array $methods): MockObject
    {
        return $this->getMockForTrait(
            $className,
            [],
            '',
            false,
            true,
            true,
            $methods
        );
    }

    protected function getMockForTypeWithMethods(string $className, array $methods): MockObject
    {
        $type = $this->getClassType($className);

        if ($type === 'interface') {
            $mock = $this->createMock($className);
        } elseif ($type === 'class') {
            $mock = $this->getMockWithMockedMethods($className, $methods);
        } elseif ($type === 'abstract') {
            $mock = $this->getMockForAbstractClassWithMockedMethods($className, $methods);
        } elseif ($type === 'trait') {
            $mock = $this->getMockForAbstractClassWithMockedMethods($className, $methods);
        } else {
            throw new \InvalidArgumentException("Invalid class type: $type");
        }
        return $mock;
    }

    /**
     * @param string $className
     * @param array $methods
     * @return MockObject|\Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface
     */
    protected function getMockObjectManager(
        $className = \Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface::class,
        $methods = []
    ): MockObject
    {
        $this->mockOm = $this->getMockForTypeWithMethods($className, $methods);

        return $this->mockOm;
    }

    /**
     * @param string $className
     * @param array $methods
     * @return MockObject|\Symfony\Component\EventDispatcher\EventDispatcher
     */
    protected function getMockDispatcher(
        $className = \Symfony\Component\EventDispatcher\EventDispatcher::class,
        $methods = []
    ): MockObject
    {
        $this->mockDispatcher = $this->getMockForTypeWithMethods($className, $methods);

        return $this->mockDispatcher;
    }

    /**
     * @param string $className
     * @param array $methods
     * @return MockObject|\Bnza\JobManagerBundle\Runner\Job\JobInterface
     */
    protected function getMockJob(
        $className = \Bnza\JobManagerBundle\Runner\Job\JobInterface::class,
        $methods = []
    ): MockObject
    {
        $this->mockJob = $this->getMockForTypeWithMethods($className, $methods);
        return $this->mockJob;
    }

    /**
     * @param string $className
     * @param array $methods
     * @param int $index
     * @return MockObject|\Bnza\JobManagerBundle\Runner\Task\TaskInterface
     */
    protected function getMockTask(
        $className = \Bnza\JobManagerBundle\Runner\Task\TaskInterface::class,
        $methods = [],
        $index = 0
    ): MockObject
    {
        $mockTask = $this->mockTasks[$index] = $this->getMockForTypeWithMethods($className, $methods);
        return $mockTask;
    }

    protected function invokeConstructor(string $class, MockObject $object, array $arguments): void
    {
        $rc = new \ReflectionClass($class);
        $constructor = $rc->getConstructor();
        $constructor->invokeArgs($object, $arguments);
    }

}
