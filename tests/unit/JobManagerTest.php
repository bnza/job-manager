<?php

namespace Bnza\JobManagerBundle\Tests\Unit;

use Bnza\JobManagerBundle\Entity\TaskEntity;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Event\TaskCreatedEvent;
use Bnza\JobManagerBundle\JobManager;
use Bnza\JobManagerBundle\Repository\TaskRepositoryInterface;
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
     * @var MockObject|TaskRepositoryInterface
     */
    private $activeTaskRepository;

    private $storedTaskRepository;

    public function setUp(): void
    {
        $this->dispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $this->activeTaskRepository = $this->getMockForAbstractClass(TaskRepositoryInterface::class);
        $this->storedTaskRepository = $this->getMockForAbstractClass(TaskRepositoryInterface::class);
        $this->jobManager = new JobManager($this->activeTaskRepository, $this->storedTaskRepository, $this->dispatcher);
    }

    public function testGenerateId()
    {
        $this->assertTrue(Uuid::isValid($this->jobManager->generateId()));
        $this->assertRegExp('/^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-(8|9|a|b)[a-f0-9]{3}\-[a-f0-9]{12}$/', $this->jobManager->generateId());
    }

    public function testGenerateIdChecksExistingUuids()
    {
        $this->storedTaskRepository->expects($this->exactly(2))->method('exists')->will($this->onConsecutiveCalls(true, false));
        $this->activeTaskRepository->expects($this->exactly(3))->method('exists')->will($this->onConsecutiveCalls(true, false, false));
        $this->jobManager->generateId();
    }

    public function testSetId()
    {
        $entity = $this->getMockForAbstractClass(TaskEntityInterface::class);
        $entity->expects($this->once())->method('setId');
        $event = $this->createStub(TaskCreatedEvent::class);
        $event->method('getTaskEntity')->willReturn($entity);
        $this->jobManager->setUpCreatedTask($event);
    }
}
