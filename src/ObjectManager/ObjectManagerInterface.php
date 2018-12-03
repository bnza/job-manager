<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
