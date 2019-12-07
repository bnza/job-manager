<?php

namespace Bnza\JobManagerBundle\Repository;

use Bnza\JobManagerBundle\Entity\TaskInfoEntity;

interface TaskRepositoryInterface
{
    /**
     * @return TaskInfoInterface
     */
    public function find(string $uuid): TaskInfoEntity;

    public function exists(string $uuid): bool;

    public function persist(TaskInfoInterface $taskInfo): void;
}
