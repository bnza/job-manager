<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\ObjectManager;

use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;

interface ObjectManagerInterface
{
    /**
     * Persists the entity (or just the given property).
     *
     * @param JobManagerEntityInterface $entity
     * @param string                    $property
     */
    public function persist(JobManagerEntityInterface $entity, string $property = ''): void;

    /**
     * Refresh the entity (or just the given property) from the persistence layer.
     *
     * @param JobManagerEntityInterface $entity
     * @param string                    $property
     *
     * @throws JobManagerEntityNotFoundException
     */
    public function refresh(JobManagerEntityInterface $entity, string $property = ''): void;

    /**
     * Finds and return a JobManagerEntityInterface with the given id.
     *
     * @param string $class
     * @param string $jobId
     * @param int    $taskNum
     *
     * @return JobManagerEntityInterface
     */
    public function find(string $class, string $jobId, int $taskNum = -1): JobManagerEntityInterface;
}
