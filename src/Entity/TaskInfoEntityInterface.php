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
     * @return string
     */
    public function getId(): string;

    /**
     * Returns the fully qualified Task class name
     *
     * @return string
     */
    public function getClass(): string;

    /**
     * Returns task's brief description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Returns the task's end float microtime() value
     *
     * @return float
     */
    public function getFinishedAt(): ?float;

    /**
     * Returns the task's start float microtime() value
     *
     * @return float
     */
    public function getStartedAt(): float;
    
    /**
     * Returns the task's status
     *
     * @return Status
     */
    public function getStatus(): Status;

    /**
     * Returns the Task's steps iterable
     * @return iterable
     */
    public function getSteps(): iterable;
}
