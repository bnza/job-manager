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
     * @var MockObject[]|\Bnza\JobManagerBundle\Entity\JobEntityInterface[]
     */
    protected $mockJobEntity = [];

    /**
     * @var MockObject[]|\Bnza\JobManagerBundle\Entity\TaskEntityInterface[]
     */
    protected $mockTaskEntity = [];

    /**
     * @var MockObject[]|\Bnza\JobManagerBundle\Runner\Status[]
     */
    protected $mockStatus = [];

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
            ->setMethods($methods ?: null)
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
            $mock = $this->getMockForTraitWithMockedMethods($className, $methods);
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
        $mock = $this->mockTasks[$index] = $this->getMockForTypeWithMethods($className, $methods);
        return $mock;
    }

    /**
     * @param string $className
     * @param array $methods
     * @param int $index
     * @return MockObject|\Bnza\JobManagerBundle\Entity\JobEntityInterface
     */
    protected function getMockJobEntity(
        $className = \Bnza\JobManagerBundle\Entity\JobEntityInterface::class,
        $methods = [],
        $index = 0
    ): MockObject
    {
        $mock = $this->mockJobEntity[$index] = $this->getMockForTypeWithMethods($className, $methods);
        return $mock;
    }

    /**
     * @param string $className
     * @param array $methods
     * @param int $index
     * @return MockObject|\Bnza\JobManagerBundle\Entity\TaskEntityInterface
     */
    protected function getMockTaskEntity(
        $className = \Bnza\JobManagerBundle\Entity\TaskEntityInterface::class,
        $methods = [],
        $index = 0
    ): MockObject
    {
        $mock = $this->mockJobEntity[$index] = $this->getMockForTypeWithMethods($className, $methods);
        return $mock;
    }

    /**
     * @param string $className
     * @param array $methods
     * @param int $index
     * @return MockObject|\Bnza\JobManagerBundle\Runner\Status
     */
    protected function getMockStatus(
        $className = \Bnza\JobManagerBundle\Runner\Status::class,
        $methods = [],
        $index = 0
    ): MockObject
    {
        $mock = $this->mockStatus[$index] = $this->getMockForTypeWithMethods($className, $methods);
        return $mock;
    }

    protected function invokeConstructor(string $class, MockObject $object, array $arguments): void
    {
        $rc = new \ReflectionClass($class);
        $constructor = $rc->getConstructor();
        $constructor->invokeArgs($object, $arguments);
    }

    public function getMockTaskAndInvokeConstructor(string $class, array $specificArgs = [], array $baseArgs = [],  array $methods = [], int $index = 0): MockObject
    {
        $mockTask = $this->getMockTask($class, $methods, $index);

        if (!isset($baseArgs[0])) {
            $baseArgs[0] = $this->mockOm ?: $this->getMockObjectManager();
        }
        if (!isset($baseArgs[1])) {
            $baseArgs[1] = $this->mockJob ?: $this->getMockJob();
        }
        if (!isset($baseArgs[2])) {
            $baseArgs[2] = (int) mt_rand(0, 100);
        }

        $this->invokeConstructor($class, $mockTask, \array_merge($baseArgs, $specificArgs));
        return $mockTask;
    }

    /**
     * Replaces string placeholder (e.g. '**mockJob**' or '**mockTask[0]**') with the corresponding mocked object
     * stored as object property (e.g. $this->mockObject or $this->mockTask[0])
     * @param array $data
     * @return array
     */
    protected function replacePlaceholderWithMockedObject(array $data): array
    {
        $pattern = '/^\*\*(?P<object>\w+)(?>\[(?P<index>\d+)\])?\*\*$/';
        foreach ($data as $key => $datum) {
            if (\is_array($datum)) {
                $data[$key] = $this->replacePlaceholderWithMockedObject($datum);
            } else {
                if (\is_string($datum)) {
                    if (preg_match($pattern, $datum, $matches)) {
                        $datum = $this->{$matches['object']};
                        if (isset($matches['index'])) {
                            $datum = $datum[$matches['index']];
                        }
                        $data[$key] = $datum;
                    }
                }
            }
        }
        return $data;
    }

}
