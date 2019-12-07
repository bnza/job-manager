<?php

namespace Bnza\JobManagerBundle\Event;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Task\TaskEvents;
use Symfony\Contracts\EventDispatcher\Event;

class TaskCreatedEvent extends Event
{
    const NAME = TaskEvents::CREATED;

    /**
     * @var TaskEntityInterface
     */
    protected $taskEntity;

    /**
     * @param TaskEntityInterface $taskEntity
     */
    public function __construct(TaskEntityInterface $taskEntity)
    {
        $this->taskEntity = $taskEntity;
    }

    public function getTaskEntity(): TaskEntityInterface
    {
        return $this->taskEntity;
    }
}
