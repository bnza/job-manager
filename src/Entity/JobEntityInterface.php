<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity;

interface JobEntityInterface extends RunnableEntityInterface
{
    public function addTask(TaskEntityInterface $task): JobEntityInterface;

    public function getTasks(): \ArrayIterator;

    public function getTask(int $num): TaskEntityInterface;

    public function clearTasks(): JobEntityInterface;
}
