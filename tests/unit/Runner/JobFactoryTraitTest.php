<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runner\JobFactoryTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobFactoryTraitTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructor()
    {
        $om = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);

        $factory = $this->getMockForTrait(JobFactoryTrait::class, [$om, $dispatcher]);
        $this->assertEquals($om, $factory->getObjectManager());
        $this->assertEquals($dispatcher, $factory->getDispatcher());
    }
}
