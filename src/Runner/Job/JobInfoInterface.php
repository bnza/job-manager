<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Job;

use Bnza\JobManagerBundle\Runner\Task\TaskInfoInterface;
use Bnza\JobManagerBundle\Runner\RunnableInfoInterface;

interface JobInfoInterface extends RunnableInfoInterface
{
    public function getTask(int $num): TaskInfoInterface;

    public function getCurrentTask(): TaskInfoInterface;
}
