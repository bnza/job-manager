<?php

namespace Bnza\JobManagerBundle;

use Bnza\JobManagerBundle\Entity\TaskEntityFactoryInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Event\TaskInitializedEvent;
use Bnza\JobManagerBundle\Repository\JobRepository;
use Bnza\JobManagerBundle\Task\TaskEvents;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class JobManager
{
    /**
     * @var JobRepository
     */
    private $jobRepository;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var TaskEntityFactoryInterface
     */
    private $taskFactory;

    /**
     * @param JobRepository $jobRepository
     * @param EventDispatcherInterface $dispatcher
     * @param TaskEntityFactoryInterface $taskFactory
     */
    public function __construct(JobRepository $jobRepository, EventDispatcherInterface $dispatcher, TaskEntityFactoryInterface  $taskFactory)
    {
        $this->jobRepository = $jobRepository;
        $this->dispatcher = $dispatcher;
        $this->taskFactory = $taskFactory;
    }

    /**
     * Returns a uuid4 string
     *
     * @return string
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

    public function registerTaskEntity(array $taskData = []): TaskEntityInterface
    {
        $taskEntity = $this->taskFactory->create($taskData);
        if (!$taskEntity->getUuid()) {
            $uuid = $this->generateId();
            $taskEntity->setUuid($uuid);
            $this->jobRepository->release($uuid);
        }
        $this->jobRepository->persist($taskEntity);
        return $taskEntity;
    }
}
