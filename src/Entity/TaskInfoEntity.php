<?php

namespace Bnza\JobManagerBundle\Entity;

use Bnza\JobManagerBundle\Status\Status;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TaskInfoEntity implements TaskInfoEntityInterface
{
    /**
     * @var string The Task id
     */
    protected $uuid;

    /**
     * @var string Fully qualified Task's class name
     */
    protected $class;

    /**
     * @var string Task's brief description
     */
    protected $description;

    /**
     * @var float Unix microtime() float
     */
    protected $startedAt;

    /**
     * @var ?float Unix microtime
     */
    protected $finishedAt;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var array Task's steps
     */
    protected $steps = [];

    /**
     * @var int The current step index (base 1 index)
     */
    protected $currentStepIndex = 0;

    /**
     * @var int The Task's step count
     */
    protected $stepsCount = 0;

    /**
     * @inheritdoc
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @inheritdoc
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @inheritdoc
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getFinishedAt(): ?float
    {
        return $this->finishedAt;
    }

    /**
     * @inheritdoc
     */
    public function getStartedAt(): ?float
    {
        return $this->startedAt;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): ?Status
    {
        return $this->status;
    }

    /**
     * {@inheritDoc}
     */
    public function getSteps(): iterable
    {
        return $this->steps;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentStepIndex(): int
    {
        return $this->currentStepIndex;
    }

    /**
     * {@inheritDoc}
     */
    public function getStepsCount(): int
    {
        return $this->stepsCount;
    }
}
