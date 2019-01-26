<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Bnza\JobManagerBundle\Runner\Task\TaskInterface;

abstract class AbstractTaskEvent extends Event
{
    /**
     * @var TaskInterface
     */
    protected $task;

    public function __construct(TaskInterface $task)
    {
        $this->task = $task;
    }

    /**
     * @return TaskInterface
     */
    public function getJob(): TaskInterface
    {
        return $this->task;
    }
}
