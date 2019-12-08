<?php

namespace Bnza\JobManagerBundle\Event;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractTaskEvent extends Event
{
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
