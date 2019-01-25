<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable\Task;

use Bnza\JobManagerBundle\Runnable\RunnableInfoInterface;

interface TaskInfoInterface extends RunnableInfoInterface
{
    public function getNum(): int;
}
