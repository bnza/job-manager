<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */


namespace Bnza\JobManagerBundle\Runnable\Job;

use Bnza\JobManagerBundle\Runnable\Task\AbstractTask;
use Bnza\JobManagerBundle\Runnable\Task\TaskInfoInterface;
use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runnable\Traits\RunnableTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractJob implements JobInterface, JobInfoInterface
{
    use RunnableTrait;
    use Traits\ParameterBagTrait;
    use Traits\JobInfoTrait;

    /**
     * @var JobEntityInterface
     */
    protected $entity;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(ObjectManagerInterface $om, EventDispatcher $dispatcher, $entity, array $parameters = [])
    {
        $this->parameters = new ParameterBag($parameters);
        $this->dispatcher = $dispatcher;
        $jobId = '';
        if (!$entity instanceof JobEntityInterface) {
            // jobId provided
            $jobId = $entity;
            $entity = $om->getEntityClass('job');
        }
        $this->setUpRunnable($om, $entity, $jobId);
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
            $this->configure();
            foreach ($this->getSteps() as $num => $taskData) {
                $this->runTask($num, $taskData);
            }
        } catch (\Throwable $e) {
            $this->error($e);
            throw $e;
        } finally {
            $this->terminate();
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
     *  [class]=>string(*) The fully qualified Task class name (MUST implements TaskInterface) (required)
     *  [arguments]=>array The Task* subclass arguments, the AbstractTask ones are provided by the function (optional)
     *  [parameters]=>array Call the provided Task* setter methods with the relative Job* getter methods. It will called before Task*::configure() (optional)
     *  [setters]=>string Call the provided Job* setter methods with the relative Task* getter methods. It will called after Task*::terminate() (optional)
     *}.
     *
     * e.g.
     *  [
     *      'class' => DummyTask,
     *      'arguments' => [ 'some value', $this->getArgument2 ],
     *      'parameters' => [
     *          ['setTaskParameter1', 'some parameter'],
     *          ['setTaskParameter1', 'getJobParameter1']
     *      ],
     *      'setters' => [
     *          ['setJobParameter1', 'some parameter'],
     *          ['setJobParameter1', 'getTaskParameter1']
     *      ]
     *
     *  ]
     * @param int $num
     * @param array $taskData
     *
     * @return AbstractTask
     */
    protected function initTask(int $num, array $taskData): AbstractTask
    {
        if (!$class = $taskData['class']) {
            throw new \InvalidArgumentException("Task class must be provided");
        }

        if (!\is_string($class)) {
            throw new \InvalidArgumentException("Task class must be a string");
        }

        if (in_array(AbstractTask::class, \class_parents($class))) {
            $arguments = [];
            if (isset($taskData['arguments'])) {
                if (\is_array($taskData['arguments'])) {
                    $arguments = $taskData['arguments'];
                } else {
                    $arguments[] = $taskData['arguments'];
                }
            }
            $this->setCurrentStepNum($num);
            $task = $this->createTask($class, $num, $arguments);
            $this->tasks[$num] = $task;

            return $task;
        }
        throw new \InvalidArgumentException("Task class must implement TaskInterface: \"$class\" does not");
    }

    protected function setJobParameters(AbstractTask $task, array $taskData)
    {
        if (isset($taskData['setters'])) {
            foreach ($taskData['setters'] as $setter) {
                $argument = $setter[1];
                $method = $setter[0];
                if (\is_callable($argument)) {
                    $argument = $argument();
                } else if (\is_string($argument) && \method_exists($task, $argument)) {
                    $argument = $task->$argument();
                }

                if (\is_callable($method)) {
                    \call_user_func($method, $argument);
                } else if (\is_string($method) && \method_exists($this, $method)) {
                    $this->$method($argument);
                } else {
                    throw new \InvalidArgumentException("Invalid setter provided: " . gettype($method));
                }
            }
        }
    }

    protected function setTaskParameters(AbstractTask $task, array $taskData)
    {
        if (isset($taskData['parameters'])) {
            foreach ($taskData['parameters'] as $parameter) {
                $argument = $parameter[1];
                $method = $parameter[0];
                if (\is_callable($argument)) {
                    $argument = $argument();
                }
                if (\is_callable($method)) {
                    \call_user_func($method, $argument);
                } else if (\is_string($method) && \method_exists($task, $method)) {
                    $task->$method($argument);
                } else {
                    throw new \InvalidArgumentException("Invalid provided: " . gettype($method));
                }
            }
        }
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
        $this->setTaskParameters($task, $taskData);
        $task->run();
        $this->setJobParameters($task, $taskData);
    }

    public function getTask(int $num): TaskInfoInterface
    {
        return $this->tasks[$num];
    }
}
