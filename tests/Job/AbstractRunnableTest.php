<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 08/12/18
 * Time: 18.10.
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Job\AbstractRunnable;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Tests\UtilTrait;

class AbstractRunnableTest extends \PHPUnit\Framework\TestCase
{
    use UtilTrait;

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

    public function testGetClass()
    {
        $runnable = $this->getAbstractRunnableMock();

        $this->assertEquals(get_class($runnable), $runnable->getClass());
    }

    public function testConstructor()
    {
        $runnable = $this->getAbstractRunnableMock(['getName']);

        $name = 'Dummy job/task name';

        $runnable
            ->method('getName')
            ->willReturn($name);

        $stepsNum = (int) rand(0, 100);

        $runnable
            ->method('countStepsNum')
            ->willReturn($stepsNum);

        $om = $this->createMock(ObjectManagerInterface::class);

        $om->expects($spy = $this->once())
            ->method('persist');

        $entity = new JobEntity();

        $reflectedClass = new \ReflectionClass(AbstractRunnable::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($runnable, [$om, $entity]);
        $this->assertEquals(\get_class($runnable), $entity->getClass());
        $this->assertEquals($name, $entity->getName());
        $this->assertEquals($stepsNum, $entity->getStepsNum());

        return [
            'entity' => $entity,
            'class' => \get_class($runnable),
            'name' => $name,
            'stepsNum' => $stepsNum,
        ];
    }

    public function propertiesProvider()
    {
        return [
            ['Class'],
            ['Name'],
            ['StepsNum'],
        ];
    }

    /**
     * @depends testConstructor
     * @dataProvider propertiesProvider
     *
     * @param array  $data
     * @param string $prop
     */
    public function testUpdateEntity(string $prop, array $data)
    {
        $this->assertEquals(
            $data[\lcfirst($prop)],
            $data['entity']->{"get$prop"}()
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You must must override this method in concrete class
     */
    public function testGetNameThrowsException()
    {
        $runnable = $this->getAbstractRunnableMock();
        $runnable->getName();
    }
}
