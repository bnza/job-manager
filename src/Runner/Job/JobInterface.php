<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */
namespace Bnza\JobManagerBundle\Runner\Job;

use Bnza\JobManagerBundle\Runner\RunnableInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface JobInterface extends RunnableInterface
{
    public function error(\Throwable $e): void;

    public function success(): void;

    public function getDispatcher(): EventDispatcherInterface;

    public function pushError(string $key, array $value, ?\Throwable $t = null);
}
