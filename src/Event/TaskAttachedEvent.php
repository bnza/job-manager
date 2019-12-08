<?php

namespace Bnza\JobManagerBundle\Event;

use Bnza\JobManagerBundle\Task\TaskEvents;

class TaskAttachedEvent extends AbstractTaskEvent
{
    const NAME = TaskEvents::ATTACHED;
}
