<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;

class TaskEntity extends AbstractJobManagerEntity implements TaskEntityInterface
{
    /**
     * @var JobEntity
     */
    private $job;

    /**
     * @var int
     */
    private $num;

    public function __construct($job, int $num = -1)
    {
        if ($job) {
            if ($job instanceof JobEntity) {
                $this->job = $job;
            } elseif (is_string($job)) {
                $this->job = new JobEntity($job);
            } else {
                throw new \InvalidArgumentException('Invalid Job parameter: only job ID string or JobEntity instance are permitted');
            }
        }

        if (-1 != $num) {
            $this->num = $num;
        }
    }

    public function getJob(): ?JobEntityInterface
    {
        return $this->job;
    }

    public function setJob(JobEntityInterface $job): TaskEntityInterface
    {
        $this->job = $job;

        return $this;
    }

    public function getNum(): int
    {
        return $this->num;
    }

    public function setNum($num): TaskEntityInterface
    {
        $this->num = (int) $num;

        return $this;
    }

    public function setName(string $name): TaskEntityInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setClass(string $class): TaskEntityInterface
    {
        $this->class = $class;

        return $this;
    }

    public function setCurrentStepNum($num): TaskEntityInterface
    {
        $this->currentStepNum = (int) $num;

        return $this;
    }

    public function setStepsNum($num): TaskEntityInterface
    {
        $this->stepsNum = (int) $num;

        return $this;
    }
}
