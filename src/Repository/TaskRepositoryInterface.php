<?php

namespace Bnza\JobManagerBundle\Repository;

use Bnza\JobManagerBundle\Entity\TaskInfoEntity;

interface TaskRepositoryInterface
{
    /**
     * @return TaskInfoInterface
     */
    public function find(string $uuid): TaskInfoEntity;

    /**
     * @return bool
     */
    public function exists(string $uuid): bool;
}
