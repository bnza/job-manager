<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\ObjectManager;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Tests\Fixture\Runner\Job\DummyJob1;
use Bnza\JobManagerBundle\Tests\Fixture\Runner\Job\DummyJob2;
use Bnza\JobManagerBundle\Runner\JobFactory;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject|ObjectManagerInterface
     */
    private $om;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var string
     */
    private $sha1;

    public function setUp()
    {
        $this->om = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $this->sha1 = sha1(microtime());
    }

    public function testMethodCreateWithoutJobId()
    {
        $factory = new JobFactory($this->om, $this->dispatcher);
        $job = $factory->create(DummyJob1::class, $this->sha1);
        $this->assertInstanceOf(DummyJob1::class, $job);
    }

    public function testMethodCreateWithJobId()
    {
        $factory = new JobFactory($this->om, $this->dispatcher);
        $job = $factory->create(DummyJob1::class, $this->sha1);
        $this->assertInstanceOf(DummyJob1::class, $job);
        $this->assertEquals($this->sha1, $job->getId());
    }

    public function testMethodCreateWithParameters()
    {
        $factory = new JobFactory($this->om, $this->dispatcher);
        $job = $factory->create(
            DummyJob2::class,
            '',
            [
                'some-parameter' => 'value1',
                'some-Other-parameter' => 'value2',
            ]
        );
        $this->assertEquals($job->getSomeParameter(), 'value1');
        $this->assertEquals($job->getSomeOtherParameter(), 'value2');
    }
}
