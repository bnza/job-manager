<?php

namespace Bnza\JobManagerBundle\Task;

use Bnza\JobManagerBundle\JobManager;

interface TaskInterface
{
    /**
     * Attach the task to the manager
     *
     * @return void
     */
    public function attach(JobManager $jobManager, ?string $uuid): void;

    public function getDescription(): string;

    public function getSteps(): iterable;

    public function getStepsCount(): int;
}
