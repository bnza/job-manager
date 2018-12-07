<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\ObjectManager;

use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;

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
     */
    public function refresh(JobManagerEntityInterface $entity, string $property = ''): void;
}
