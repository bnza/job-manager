<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Runner\RunnableTrait;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Tests\UtilsTrait;

class RunnableTraitTest extends \PHPUnit\Framework\TestCase
{
    use UtilsTrait;

    private $name = 'Dummy job/task name ';

    private $description = 'Dummy job/task description ';

    private $stepsNum;

    public function setUp()
    {
        $this->stepsNum = (int) rand(0, 100);
        $this->name .= $this->stepsNum;
    }

    public function getAbstractRunnableMock($methods = [])
    {
        return $this->getMockForAbstractClass(
            AbstractRunnable::class,
            [],
            '',
            false,
            true,
            true,
            $methods
        );
    }

    public function callConstructor($runnable, $om = null, $entity = null)
    {
        $om = $om ?: $this->createMock(ObjectManagerInterface::class);
        $entity = $entity ?: new JobEntity();
        $reflectedClass = new \ReflectionClass(AbstractRunnable::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($runnable, [$om, $entity]);
    }

    public function testGetClassWillReturnClassName()
    {
        $runnable = $this->getRunnableTraitMock();

        $this->assertEquals(get_class($runnable), $runnable->getClass());
    }

    public function testUpdateEntityWillSetUpEntity()
    {
        $runnable = $this->getRunnableTraitMock(['getEntity','getName', 'getStepsNum', 'getDescription']);

        $runnable
            ->method('getName')
            ->willReturn($this->name);

        $runnable
            ->method('getDescription')
            ->willReturn($this->description);

        $runnable
            ->method('getStepsNum')
            ->willReturn($this->stepsNum);

        $entity = $this->getMockForAbstractClass(
            RunnableEntityInterface::class
            );

        $entity->expects($this->once())->method('setClass')->with(\get_class($runnable));
        $entity->expects($this->once())->method('setName')->with($this->name);
        $entity->expects($this->once())->method('setDescription')->with($this->description);

        $runnable
            ->expects($this->once())
            ->method('getEntity')
            ->willReturn($entity);

        $method = $this->getNonPublicMethod($runnable, 'updateEntity');
        $method->invoke($runnable);
    }

    public function testGetStepsNumWithStepsArrayWillReturnRightValue()
    {
        $runnable = $this->getRunnableTraitMock(['getName', 'getSteps']);

        $runnable
            ->method('getSteps')
            ->willReturn(['a', 'b', 'c']);

        $this->assertEquals(3, $runnable->getStepsNum());
    }

    public function testGetStepsNumWithStepsGeneratorWillReturnRightValue()
    {
        $runnable = $this->getRunnableTraitMock(['getName', 'getSteps']);

        $runnable
            ->method('getSteps')
            ->will($this->returnCallback(
                function () {
                    yield 'a';
                    yield 'b';
                    yield 'c';
                }
            ));

        $this->assertEquals(3, $runnable->getStepsNum());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You must must override "getName" method in concrete class
     */
    public function testGetNameFunctionThrowsException()
    {
        $runnable = $this->getRunnableTraitMock();
        $runnable->getName();
    }

    /**
     * @testWith    [""]
     *              ["prop1"]
     * @param string $prop
     * @throws \ReflectionException
     */
    public function testPersistWillPersistsObjectManager(string $prop)
    {
        $om = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $om
            ->expects($this->once())
            ->method('persist')
            ->with(
                $this->isInstanceOf(RunnableEntityInterface::class),
                $prop
            );

        $runnable = $this->getRunnableTraitMockWithPersist($om);
        $runnable->persist($prop);
    }

    public function testNextWillPersistsRightValue()
    {
        $entity = $this->getMockForAbstractClass(RunnableEntityInterface::class);

        $entity
            ->expects($this->once())
            ->method('getCurrentStepNum')
            ->willReturn($this->stepsNum);

        $entity
            ->expects($this->once())
            ->method('setCurrentStepNum')
            ->with($this->stepsNum + 1);

        $om = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $om
            ->expects($this->once())
            ->method('persist')
            ->with(
                $entity,
                'current_step_num'
            );

        $runnable = $this->getRunnableTraitMockWithPersist($om, $entity);
        $method = $this->getNonPublicMethod($runnable, 'next');
        $method->invoke($runnable);
    }

    protected function getRunnableTraitMock(array $methods = [])
    {
        $runnable = $this->getMockForTrait(
            RunnableTrait::class,
            [],
            '',
            false,
            false,
            true,
            $methods
        );

        return $runnable;
    }

    protected function getRunnableTraitMockWithPersist(ObjectManagerInterface $om = null, RunnableEntityInterface $entity = null)
    {
        $runnable = $this->getRunnableTraitMock(['getObjectManager', 'getEntity']);

        if (!$om) {
            $om = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        }

        if (!$entity) {
            $entity = $this->getMockForAbstractClass(RunnableEntityInterface::class);
        }

        $runnable->method('getObjectManager')->willReturn($om);
        $runnable->method('getEntity')->willReturn($entity);

        return $runnable;
    }
}
