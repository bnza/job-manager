<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\ObjectManager;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;

interface ObjectManagerInterface
{
    /**
     * Persists the entity (or just the given property).
     *
     * @param RunnableEntityInterface $entity
     * @param string                  $property
     */
    public function persist(RunnableEntityInterface $entity, string $property = ''): void;

    /**
     * Refresh the entity (or just the given property) from the persistence layer.
     *
     * @param RunnableEntityInterface $entity
     * @param string                  $property
     *
     * @throws JobManagerEntityNotFoundException
     */
    public function refresh(RunnableEntityInterface $entity, string $property = ''): void;

    /**
     * Finds and return a RunnableEntityInterface with the given id.
     *
     * @param string $class
     * @param string $jobId
     * @param int    $taskNum
     *
     * @return RunnableEntityInterface
     */
    public function find(string $class, string $jobId, int $taskNum = -1): RunnableEntityInterface;

    /**
     * Return the right entity class for the current ObjectManager
     * @param string $type valid values are 'job', 'task'
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getEntityClass(string $type): string;
}
