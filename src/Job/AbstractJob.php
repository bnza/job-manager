<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */


namespace Bnza\JobManagerBundle\Job;

use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractJob extends AbstractRunnable implements JobInterface, JobInfoInterface
{
    use JobInfoTrait;

    /**
     * @var JobEntityInterface
     */
    protected $entity;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var ParameterBag
     */
    protected $parameters;

    public function __construct(ObjectManagerInterface $om, EventDispatcher $dispatcher, $entity, array $parameters = [])
    {
        $this->parameters = new ParameterBag($parameters);
        $this->dispatcher = $dispatcher;
        if (!$entity || is_string($entity)) {
            $entity = new JobEntity($entity);
        }
        parent::__construct($om, $entity);
    }

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    public function getParameter(string $key, bool $throw = true)
    {
        $pb = $this->getParameters();
        if ($pb->has($key))
        {
            return $pb->get($key);
        }
        if ($throw) {
            throw new \LogicException("Parameter \"$key\" is not set");
        }
    }

    public function getDispatcher(): EventDispatcher
    {
        return $this->dispatcher;
    }

    /**
     * @return JobEntityInterface
     */
    protected function getEntity()
    {
        return $this->entity;
    }

    protected function getTasks(): array
    {
        return $this->tasks;
    }


    final public function run(): void
    {
        $this->running();
        try {
            foreach ($this->getSteps() as $num => $taskData) {
                $this->runTask($num, $taskData);
            }
        } catch (\Throwable $e) {
            $this->error($e);
            return;
        }
        $this->success();
    }

    public function rollback(): void
    {
        foreach ($this->getTasks() as $num => $task) {
            $task->rollback();
        }
    }

    /**
     * Initializes a new TaskInterface instance using $taskData array which is in the form
     * array(3) {
     *  [0]=>string(*) The fully qualified Task class name (MUST implements TaskInterface) (required)
     *  [1]=>array The Task* subclass arguments, the AbstractTask ones are provided by the function (optional)
     *  [2]=>string The name of the function used for set task's run() return value in the current job
     *}.
     *
     * @param int   $num
     * @param array $taskData
     *
     * @return AbstractTask
     */
    protected function initTask(int $num, array $taskData): AbstractTask
    {
        $class = $taskData[0];
        if (in_array(AbstractTask::class, \class_parents($class))) {
            $arguments = [];
            if (isset($taskData[1])) {
                if (\is_array($taskData[1]))
                {
                    $arguments = $taskData[1];
                } else {
                    $arguments[] = $taskData[1];
                }
            }
            $this->setCurrentStepNum($num);
            $task = $this->createTask($class, $num, $arguments);
            $this->tasks[$num] = $task;

            return $task;
        }
        throw new \InvalidArgumentException("Task class must implement TaskInterface: \"$class\" does not");
    }

    protected function createTask(string $class, $num, $arguments): AbstractTask
    {
        return new $class($this->getObjectManager(), $this, $num, ...$arguments);
    }

    public function error(\Throwable $e): void
    {
        $this->getEntity()->setError($e);
        $this->getEntity()->getStatus()->error();
        $this->persist();
        $this->rollback();
        $this->getDispatcher()->dispatch(JobEndedEvent::NAME, new JobEndedEvent($this));
    }

    public function success(): void
    {
        $this->getEntity()->getStatus()->success();
        $this->persist('status');
        $this->getDispatcher()->dispatch(JobEndedEvent::NAME, new JobEndedEvent($this));
    }

    protected function running(): void
    {
        $this->getEntity()->getStatus()->run();
        $this->persist('status');
        $this->getDispatcher()->dispatch(JobStartedEvent::NAME, new JobStartedEvent($this));
    }

    protected function runTask(int $num, array $taskData)
    {
        $task = $this->initTask($num, $taskData);
        $returnValue = $task->run();
        if (isset($taskData[2])) {
            call_user_func([$this, $taskData[2]], $returnValue);
        }
    }

    public function getTask(int $num): TaskInfoInterface
    {
        return $this->tasks[$num];
    }
}
