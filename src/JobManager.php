<?php

namespace Bnza\JobManagerBundle;

use Bnza\JobManagerBundle\Event\TaskCreatedEvent;
use Bnza\JobManagerBundle\Repository\TaskRepositoryInterface;
use Bnza\JobManagerBundle\Task\TaskEvents;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobManager implements EventSubscriberInterface
{
    private $activeTaskRepository;
    private $storedTaskRepository;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskEvents::CREATED => [
              ['setTaskId', 200] //HIGH priority
            ]
          ];
    }

    /**
     * @param TaskRepositoryInteface $activeTaskRepository
     * @param TaskRepositoryInterface $storedTaskRepository
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(TaskRepositoryInterface $activeTaskRepository, TaskRepositoryInterface $storedTaskRepository, EventDispatcherInterface $dispatcher)
    {
        $this->activeTaskRepository = $activeTaskRepository;
        $this->storedTaskRepository = $storedTaskRepository;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns a uuid4 string
     *
     * @return void
     */
    public function generateId(): string
    {
        $uuid = Uuid::uuid4()->toString();
        $activeTaskExist = $this->activeTaskRepository->exists($uuid);
        if (!$activeTaskExist && !$this->storedTaskRepository->exists($uuid)) {
            return $uuid;
        }
        return $this->generateId();
    }

    /**
     * Subscribed event callback
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function setTaskId(TaskCreatedEvent $event)
    {
        if (!$event->getTaskEntity()->getId()) {
            $event->getTaskEntity->setId($this->generateId());
        }
    }
}
