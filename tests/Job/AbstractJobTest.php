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
use Symfony\Component\EventDispatcher\EventDispatcher;

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

        $mockOm
            ->method('find')
            ->willReturn($mockEntity);

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

        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $reflectedClass = new \ReflectionClass(AbstractJob::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($mockJob, [$mockOm, $mockDispatcher, $mockEntity]);

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

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['running', 'runTask', 'success', 'getSteps']
        );

        $mockJob->expects($this->once())
            ->method('running');

        $mockJob->expects($this->once())
            ->method('success');

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['TaskClass1'],
                ['TaskClass2']
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('runTask')
            ->withConsecutive(
                [0, ['TaskClass1']],
                [1, ['TaskClass2']]
            );

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
            ['getObjectManager', 'getEntity', 'getSteps', 'success', 'running']
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

    public function testInitTaskException()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getDispatcher', 'error']
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

        $mockJob->expects($this->once())
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $mockJob->expects($this->once())
            ->method('error')
        ->with($this->isInstanceOf(\InvalidArgumentException::class));

        $mockJob->run();
    }

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

        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getTasks', 'initTask', 'getDispatcher']
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

        $mockJob->expects($this->any())
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

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
