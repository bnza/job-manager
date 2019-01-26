<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner;

use Bnza\JobManagerBundle\Info\InfoInterface;

interface RunnableInterface extends InfoInterface
{
    /**
     * @param string $prop
     * @return Interface
     */
    public function persist(string $prop = '');

    public function getSteps(): iterable;

    public function rollback(): void;

    public function run(): void;
}
