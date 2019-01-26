<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Task;

use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\Event\TaskStartedEvent;
use Bnza\JobManagerBundle\Event\TaskEndedEvent;
use Bnza\JobManagerBundle\Event\TaskStepEndedEvent;
use Bnza\JobManagerBundle\Event\TaskStepStartedEvent;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Runner\RunnableTrait;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

abstract class AbstractTask implements TaskInterface
{
    use RunnableTrait;

    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var int
     */
    protected $stepInterval = 1;

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

    public function run(): void
    {
        $dispatcher = $this->getJob()->getDispatcher();
        $dispatcher->dispatch(TaskStartedEvent::NAME, new TaskStartedEvent($this));
        $this->configure();
        $stepInterval = $this->getStepInterval();
        /**
         * Check whether TaskStep*Event would be dispatched.
         * @param int $i
         * @return bool
         */
        $dispatchStep = function (int $i) use ($stepInterval) {
          if ($stepInterval === 1) {
              return true;
          } else if ($stepInterval === 0) {
              return false;
          } else {
              return !(bool) ($i % $stepInterval);
          }
        };
        $stepStartedEvent = new TaskStepStartedEvent($this);
        $stepEndedEvent = new TaskStepEndedEvent($this);
        foreach ($this->getSteps() as $i => $arguments) {
            if ($doDispatchStep = $dispatchStep($i)) {
                $dispatcher->dispatch(TaskStepStartedEvent::NAME, $stepStartedEvent);
            }
            $this->executeStep($arguments);
            $this->next();
            if ($doDispatchStep) {
                $dispatcher->dispatch(TaskStepEndedEvent::NAME, $stepEndedEvent);
            }
        }
        $this->terminate();
        $dispatcher->dispatch(TaskEndedEvent::NAME, new TaskEndedEvent($this));
    }

    /**
     * Rollback function, just an empty placeholder.
     * MUST be implemented when needed
     */
    public function rollback(): void
    {}

    public function getJob(): JobInterface
    {
        return $this->job;
    }

    /**
     * @return int
     */
    public function getStepInterval(): int
    {
        return $this->stepInterval;
    }

    /**
     * @param int $stepInterval
     */
    public function setStepInterval(int $stepInterval): void
    {
        $this->stepInterval = $stepInterval;
    }

    protected function executeStep(array $arguments): void
    {
        throw new \LogicException('You must must override "executeStep" method in concrete class');
    }

}
