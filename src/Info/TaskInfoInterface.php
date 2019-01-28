<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Info;

interface TaskInfoInterface extends InfoInterface
{
    public function getNum(): int;

    /**
     * @return JobInfoInterface
     */
    public function getJob();
}
