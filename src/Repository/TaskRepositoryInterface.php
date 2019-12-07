<?php

namespace Bnza\JobManagerBundle\Repository;

use Bnza\JobManagerBundle\Entity\TaskInfoEntity;
use Bnza\JobManagerBundle\Entity\TaskInfoEntityInterface;

interface TaskRepositoryInterface
{
    /**
     * @return TaskInfoEntityInterface
     */
    public function find(string $uuid): TaskInfoEntityInterface;

    public function exists(string $uuid): bool;

    public function persist(TaskInfoEntityInterface $taskInfo): void;
}
