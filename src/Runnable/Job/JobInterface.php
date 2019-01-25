<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */
namespace Bnza\JobManagerBundle\Runnable\Job;

use Bnza\JobManagerBundle\Runnable\RunnableInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface JobInterface extends RunnableInterface
{
    public function error(\Throwable $e): void;

    public function success(): void;

    public function getDispatcher(): EventDispatcher;

    public function run(): void;
}
