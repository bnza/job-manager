<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Task;

use Bnza\JobManagerBundle\Job\RunnableInterface;

interface TaskInterface extends RunnableInterface, TaskInfoInterface
{
    public function getReturnValue();

    public function run();
}
