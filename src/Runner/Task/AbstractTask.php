<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Task;

use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
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

    /**
     * @var string
     */
    protected $description = '';

    abstract public function getDefaultDescription(): string;

    abstract protected function executeStep(array $arguments): void;

    public function __construct(ObjectManagerInterface $om, JobInterface $job, int $num)
    {
        $this->job = $job;
        $entity = new TaskEntity($job->getId(), $num);
        $this->setUpRunnable($om, $entity);
        $this->persist();
    }

    public function getNum(): int
    {
        return $this->getEntity()->getNum();
    }

    /**
     * @throws JobManagerCancelledJobException
     */
    public function run(): void
    {
        $this->setStepsNum();
        $dispatcher = $this->getJob()->getDispatcher();
        $dispatcher->dispatch( new TaskStartedEvent($this),TaskStartedEvent::NAME);
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
                $dispatcher->dispatch( $stepStartedEvent, TaskStepStartedEvent::NAME);
            }
            if ($this->isCancelled()) {
                throw new JobManagerCancelledJobException();
            }
            $this->executeStep($arguments);
            $this->next();
            if ($doDispatchStep) {
                $dispatcher->dispatch($stepEndedEvent, TaskStepEndedEvent::NAME);
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

    public function getDescription(): string
    {
        return $this->description ?: $this->getDefaultDescription();
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        $this->getEntity()->setDescription($description);
    }

    protected function setStepsNum()
    {
        $this->getEntity()->setStepsNum($this->getStepsNum());
        $this->persist('steps_num');
    }

}
