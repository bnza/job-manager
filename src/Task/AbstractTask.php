<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Task;

use Bnza\JobManagerBundle\Job\JobInterface;
use Bnza\JobManagerBundle\Event\TaskStartedEvent;
use Bnza\JobManagerBundle\Event\TaskEndedEvent;
use Bnza\JobManagerBundle\Event\TaskStepEndedEvent;
use Bnza\JobManagerBundle\Event\TaskStepStartedEvent;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Job\Traits\Job\RunnableTrait;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

abstract class AbstractTask implements TaskInterface
{
    use RunnableTrait;

    /**
     * @var JobInterface
     */
    protected $job;

    public function __construct(ObjectManagerInterface $om, JobInterface $job, int $num)
    {
        $this->job = $job;
        $entity = new TaskEntity($job->getId(), $num);
        $this->setUpRunnableInfo($om, $entity);
    }

    public function getNum(): int
    {
        return $this->getEntity()->getNum();
    }

    /**
     * Setup function, just an empty placeholder.
     * MUST be implemented when needed
     */
    protected function configure(): void
    {}

    /**
     * Teardown function, just an empty placeholder.
     * MUST be implemented when needed
     */
    protected function terminate(): void
    {}

    public function run()
    {
        $dispatcher = $this->getJob()->getDispatcher();
        $dispatcher->dispatch(TaskStartedEvent::NAME, new TaskStartedEvent($this));
        $this->configure();
        $stepStartedEvent = new TaskStepStartedEvent($this);
        $stepEndedEvent = new TaskStepEndedEvent($this);
        foreach ($this->getSteps() as $step) {
            $dispatcher->dispatch(TaskStepStartedEvent::NAME, $stepStartedEvent);
            call_user_func_array($step[0], $step[1]);
            $this->next();
            $dispatcher->dispatch(TaskStepEndedEvent::NAME, $stepEndedEvent);
        }
        $this->terminate();
        $dispatcher->dispatch(TaskEndedEvent::NAME, new TaskEndedEvent($this));
        return $this->getReturnValue();
    }

    /**
     * Rollback function, just an empty placeholder.
     * MUST be implemented when needed
     */
    public function rollback(): void
    {}

    /**
     * getReturnValue function, just an empty placeholder.
     * MUST be implemented when needed
     */
    public function getReturnValue()
    {
    }

    public function getJob(): JobInterface
    {
        return $this->job;
    }
}
