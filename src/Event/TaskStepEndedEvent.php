<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Event;


class TaskStepEndedEvent extends AbstractTaskEvent
{
    const NAME = 'bnza.job_manager.task.step.ended';
}
