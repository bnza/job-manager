<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job\Traits\Job;

use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;
use Bnza\JobManagerBundle\Task\TaskInfoInterface;
use Bnza\JobManagerBundle\Task\AbstractTask;
use Bnza\JobManagerBundle\Task\TaskInfo;

trait JobInfoTrait
{
    /**
     * Registered and instantiated tasks. Used by rollback operations
     * @var AbstractTask[]
     */
    protected $tasks = [];

    /**
     * @param int $num
     *
     * @return TaskInfoInterface
     *
     * @throws \RuntimeException
     * @throws JobManagerEntityNotFoundException
     */
    public function getTask(int $num): TaskInfoInterface
    {
        if (!\array_key_exists($num, $this->tasks)) {
            $this->tasks[$num] = new TaskInfo($this->getObjectManager(), $this->getEntity()->getTask($num));
        }

        return $this->tasks[$num];
    }

    public function getCurrentTask(): TaskInfoInterface
    {
        return $this->getTask($this->getCurrentStepNum());
    }
}
