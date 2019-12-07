<?php

namespace Bnza\JobManagerBundle;

use Bnza\JobManagerBundle\Event\TaskCreatedEvent;
use Bnza\JobManagerBundle\Repository\JobRepository;
use Bnza\JobManagerBundle\Task\TaskEvents;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobManager implements EventSubscriberInterface
{
    /**
     * @var JobRepository
     */
    private $jobRepository;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            TaskEvents::CREATED => [
              ['onTaskCreated', 200] //HIGH priority
            ]
          ];
    }

    /**
     * @param TaskRepositoryInteface $activeTaskRepository
     * @param TaskRepositoryInterface $storedTaskRepository
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(JobRepository $jobRepository, EventDispatcherInterface $dispatcher)
    {
        $this->jobRepository = $jobRepository;
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
        if (!$this->jobRepository->existsUuid($uuid)) {
            $this->jobRepository->lock($uuid);
            return $uuid;
        }
        return $this->generateId();
    }

    /**
     * Subscribed event callback
     *
     * @SuppressWarnings(PHPMD.UnusedPublicMethod)
     */
    public function onTaskCreated(TaskCreatedEvent $event)
    {
        if (!$event->getTaskEntity()->getId()) {
            $event->getTaskEntity()->setId($this->generateId());
        }
    }
}