<?php

namespace Bnza\JobManagerBundle\Repository;

use Bnza\JobManagerBundle\Event\TaskCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * ActiveTaskRepository are intedend to manage ONLY active tasks persisting their info
 * in a shareable way beetween different processes.
 *
 * The interacction between the actual task's' instaces happens through the Symfony EventDispatcher
 * component. Class implementations of this interface must handle the following Events:
 *
 * {@link \Bnza\JobManagerBundle\Task\TaskEvents::CREATE} => {@link onTaskCreated}
 *
 *
 */
interface ActiveTaskRepositoryInterface extends TaskRepositoryInterface, EventSubscriberInterface
{
    /**
     * Locks the given uuid in order to avoid (very improbabile) collisions
     *
     * @param string $uuid The task uuid to lock
     * @return bool Wheher the uuid is locked
     */
    public function lock(string $uuid): bool;

    /**
     * Releases the lock on the given uuid
     *
     * @param string $uuid The task uuid to release
     * @return bool
     */
    public function release(string $uuid): bool;

    /**
     * @param string $uuid The task uuid to release
     * @return bool
     */
    public function isLocked(string $uuid): bool;

    /**
     * Should {@link persist} task's info and {@link release} the lock
     * @param TaskCreatedEvent $event
     */
    public function onTaskCreated(TaskCreatedEvent $event): void;
}
