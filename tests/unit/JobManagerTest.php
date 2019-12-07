<?php

namespace Bnza\JobManagerBundle\Tests\Unit;

use Bnza\JobManagerBundle\Entity\TaskEntity;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Event\TaskCreatedEvent;
use Bnza\JobManagerBundle\JobManager;
use Bnza\JobManagerBundle\Repository\JobRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class JobManagerTest extends TestCase
{
    /**
     * @var JobManager
     */
    private $jobManager;

    private $dispatcher;

    /**
     * @var MockObject|JobRepository
     */
    private $jobRepository;

    public function setUp(): void
    {
        $this->dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $this->jobRepository= $this->createMock(JobRepository::class);
        $this->jobManager = new JobManager($this->jobRepository, $this->dispatcher);
    }

    public function testGenerateId()
    {
        $this->assertTrue(Uuid::isValid($this->jobManager->generateId()));
    }

    public function testGenerateIdChecksExistingUuids()
    {
        $this->jobRepository
             ->expects($this->exactly(2))
             ->method('existsUuid')
             ->willReturnOnConsecutiveCalls(true, false);
        $this->jobManager->generateId();
    }

    public function testSetUuid()
    {
        $entity = $this->getMockForAbstractClass(TaskEntityInterface::class);
        $entity->expects($this->once())->method('setUuid');
        $event = $this->createMock(TaskCreatedEvent::class);
        $event->method('getTaskEntity')->willReturn($entity);
        $this->jobManager->onTaskCreated($event);
    }
}
