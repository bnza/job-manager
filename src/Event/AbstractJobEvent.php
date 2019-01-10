<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Bnza\JobManagerBundle\Job\JobInterface;

abstract class AbstractJobEvent extends Event
{
    /**
     * @var JobInterface
     */
    protected $job;

    public function __construct(JobInterface $job)
    {
        $this->job = $job;
    }

    /**
     * @return JobInterface
     */
    public function getJob(): JobInterface
    {
        return $this->job;
    }
}
