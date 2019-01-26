<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Task;

use Bnza\JobManagerBundle\Runner\RunnableInfoInterface;

interface TaskInfoInterface extends RunnableInfoInterface
{
    public function getNum(): int;
}
