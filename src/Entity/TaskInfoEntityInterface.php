<?php

namespace Bnza\JobManagerBundle\Entity;

use Bnza\JobManagerBundle\Status\Status;

/**
 * Task DTO class
 */
interface TaskInfoEntityInterface
{

    /**
     * Returns the Task id
     *
     * @return null|string
     */
    public function getUuid(): ?string;

    /**
     * Returns the fully qualified Task class name
     *
     * @return null|string
     */
    public function getClass(): ?string;

    /**
     * Returns task's brief description
     *
     * @return null|string
     */
    public function getDescription(): ?string;

    /**
     * Returns the task's end float microtime() value
     *
     * @return null|float
     */
    public function getFinishedAt(): ?float;

    /**
     * Returns the task's start float microtime() value
     *
     * @return null|float
     */
    public function getStartedAt(): ?float;

    /**
     * Returns the task's status
     *
     * @return null|Status
     */
    public function getStatus(): ?Status;

    /**
     * @return iterable Task's steps iterable
     */
    public function getSteps(): iterable;

    /**
     * @return int The current step index (base 1 index)
     */
    public function getCurrentStepIndex(): int;

    /**
     * @return int The Task's steps number either subtask or unit task steps
     */
    public function getStepsCount(): int;
}
