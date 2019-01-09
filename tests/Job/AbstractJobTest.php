<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Job\AbstractJob;
use Bnza\JobManagerBundle\Job\AbstractTask;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Job\Status;

class DummyTask extends AbstractTask
{
    public function getName(): string
    {
        return 'DummyTask name';
    }

    public function getSteps(): iterable
    {
        return [
          [$this, 'runMethod'], ['arg0', 'arg1']
        ];
    }

    public function run(): void
    {}
}

class DummyNonTask
{

}


class AbstractJobTest extends \PHPUnit\Framework\TestCase
{
    public function testConstructorRun()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockTask = $this->createMock(AbstractTask::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getName', 'getSteps', 'initTask']
        );

        $mockJob
            ->method('getName')
            ->willReturn('Mocked Job Name');

        $reflectedClass = new \ReflectionClass(AbstractJob::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($mockJob, [$mockOm, $mockEntity]);

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2']
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('initTask')
            ->willReturn($mockTask);

        $mockJob->run();
    }

    public function testRun()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockTask = $this->createMock(AbstractTask::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'initTask']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2']
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('initTask')
            ->willReturn($mockTask);

        $mockJob->run();
    }

    public function testInitTask()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'success']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                [DummyTask::class]
            ]);

        $mockJob->expects($this->once())
            ->method('success');

        $mockJob->run();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /Task class must implement TaskInterface: ".+" does not/
     */
    public function testInitTaskException()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                [DummyNonTask::class]
            ]);

        $mockJob->run();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage  Dummy exception
     */
    public function testRollback()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockTask = $this->createMock(AbstractTask::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getTasks', 'initTask']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2']
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('initTask')
            ->willReturn($mockTask);

        $mockJob->expects($this->once())
            ->method('getTasks')
            ->willReturn([$mockTask, $mockTask]);

        $mockTask
            ->expects($this->exactly(2))
            ->method('run')
            ->will(
                $this->onConsecutiveCalls(
                    true,
                    $this->throwException(new \Exception('Dummy exception'))
                )
            );

        $mockTask
            ->expects($this->exactly(2))
            ->method('rollback');

        $mockJob->run();
    }
}
