<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Event;


class JobEndedEvent extends AbstractJobEvent
{
    const NAME = 'bnza.job_manager.job.ended';
}
