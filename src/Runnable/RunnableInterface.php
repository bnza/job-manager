<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable;

interface RunnableInterface extends RunnableInfoInterface
{
    /**
     * @param string $prop
     * @return RunnableInterface
     */
    public function persist(string $prop = '');

    public function getSteps(): iterable;

    public function rollback(): void;

    public function run(): void;
}
