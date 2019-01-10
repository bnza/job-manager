<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */
namespace Bnza\JobManagerBundle\Job;

use Symfony\Component\EventDispatcher\EventDispatcher;

interface JobInterface extends RunnableInterface
{
    public function error(\Throwable $e): void;

    public function success(): void;

    public function getDispatcher(): EventDispatcher;
}
