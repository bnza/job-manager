<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Task;

use Bnza\JobManagerBundle\Runner\RunnableInterface;
use Bnza\JobManagerBundle\Info\TaskInfoInterface;

interface TaskInterface extends RunnableInterface, TaskInfoInterface
{
    public function getStepInterval(): int;

    public function setStepInterval(int $interval): void;

    public function setDescription(string $description): void;
}
