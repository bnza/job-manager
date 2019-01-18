<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;

interface RunnableInterface extends RunnableInfoInterface
{
    public function persist(string $prop = ''): RunnableInterface;

    public function getSteps(): iterable;

    public function rollback(): void;
}
