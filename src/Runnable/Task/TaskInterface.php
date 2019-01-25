<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable\Task;

use Bnza\JobManagerBundle\Runnable\RunnableInterface;

interface TaskInterface extends RunnableInterface, TaskInfoInterface
{
    public function getReturnValue();

    public function run();
}
