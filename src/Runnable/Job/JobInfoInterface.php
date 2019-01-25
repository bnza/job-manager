<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable\Job;

use Bnza\JobManagerBundle\Runnable\Task\TaskInfoInterface;
use Bnza\JobManagerBundle\Runnable\RunnableInfoInterface;

interface JobInfoInterface extends RunnableInfoInterface
{
    public function getTask(int $num): TaskInfoInterface;

    public function getCurrentTask(): TaskInfoInterface;
}
