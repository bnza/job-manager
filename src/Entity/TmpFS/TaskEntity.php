<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Runner\Status;

class TaskEntity extends AbstractRunnableEntity implements TaskEntityInterface
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
            if ($job instanceof JobEntityInterface) {
                $this->job = $job;
            } elseif (is_string($job)) {
                $this->job = new JobEntity($job);
            } else {
                throw new \InvalidArgumentException('Invalid Runnar parameter: only job ID string or JobEntity instance are permitted');
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

    public function setName(string $name): RunnableEntityInterface
    {
        $this->name = $name;

        return $this;
    }

    public function setClass(string $class): RunnableEntityInterface
    {
        $this->class = $class;

        return $this;
    }

    public function setCurrentStepNum($num): RunnableEntityInterface
    {
        $this->currentStepNum = (int) $num;

        return $this;
    }

    public function setStepsNum($num): RunnableEntityInterface
    {
        $this->stepsNum = (int) $num;

        return $this;
    }

    public function getId(): string
    {
        return $this->getJob()->getId().'.'.$this->getNum();
    }

    public function getStatus(): Status
    {
        return $this->getJob()->getStatus();
    }

    public function getError(): string
    {
        return $this->getJob()->getError();
    }

    public function setError($error): RunnableEntityInterface
    {
        $this->getJob()->setError($error);

        return $this;
    }

    public function setStatus($status): RunnableEntityInterface
    {
        $this->getJob()->setStatus($status);

        return $this;
    }
}
