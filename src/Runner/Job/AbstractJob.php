<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */


namespace Bnza\JobManagerBundle\Runner\Job;

use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
use Bnza\JobManagerBundle\Info\JobInfoInterface;
use Bnza\JobManagerBundle\Info\JobInfoTrait;
use Bnza\JobManagerBundle\Runner\Task\AbstractTask;
use Bnza\JobManagerBundle\Info\TaskInfoInterface;
use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runner\RunnableTrait;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

abstract class AbstractJob implements JobInterface, JobInfoInterface
{
    use RunnableTrait {
        isCancelled as protected infoIsCancelled;
    }
    use ParameterBagTrait;
    use JobInfoTrait;

    /**
     * @var JobEntityInterface
     */
    protected $entity;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function __construct(ObjectManagerInterface $om, EventDispatcher $dispatcher, $entity = '', array $parameters = [])
    {
        $this->setParameterBag($parameters);
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
                if ($this->isCancelled()) {
                    throw new JobManagerCancelledJobException();
                }
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

                foreach ($arguments as $i => $argument) {
                    if (\is_callable($argument)) {
                        $arguments[$i] = $argument();
                    }
                }
            }
            $this->setCurrentStepNum($num);
            $task = $this->createTask($class, $num, $arguments);
            $this->tasks[$num] = $task;

            return $task;
        }
        throw new \InvalidArgumentException("Task class must implement TaskInterface: \"$class\" does not");
    }

    /**
     * This is just a method stub. Must be implemented in subclasses when needed
     * @param array $parameters
     * @throws \InvalidArgumentException
     */
    protected function checkConstructorParameters(array $parameters) {

    }

    /**
     * @param array $parameters
     * @throws \InvalidArgumentException
     */
    protected function setParameterBag(array $parameters)
    {
        $this->checkConstructorParameters($parameters);
        $this->parameters = new ParameterBag($parameters);
    }

    /**
     * Calls the provided callable and returns value. If $args is also a callable use the return value as $param_arr of
     * call_user_func_array() function
     * @param callable $callable Any valid callable
     * @param array $args The $callable arguments
     * @param array $getterArgs This value is used as callable arguments if $args is a callable
     * @return mixed The callable's return value
     */
    protected function callCallable(callable $callable, $args = [], $getterArgs = [])
    {
        if (is_callable($args)) {
            $getterArgs = \is_array($getterArgs) ? $getterArgs : [$getterArgs];
            $args = \call_user_func_array($args, $getterArgs);
        }

        if (!\is_array($args)) {
            $args = [$args];
        }

        return \call_user_func_array($callable, $args);
    }

    /**
     * @param AbstractTask $task
     * @param $arguments
     * @return mixed the arguments used by Abstract::setJobParameters() internal function call
     */
    protected function getSetJobParameterArguments(AbstractTask $task, $arguments)
    {
        $getterArgs = isset($arguments[2]) ? $arguments[2] : [];
        $callableArgs = [];

        if (
            \is_array($arguments)
            && isset($arguments[0])
            && \is_callable($arguments[0])
        ) {
            $callable = $arguments[0];
            $callableArgs = $arguments[1];          // execute $arguments[0]($arguments[1])
        } else if (
            \is_array($arguments)
            && isset($arguments[0])
            && \is_string($arguments[0])
            && \method_exists($task, $arguments[0])
        ) {
            $callable = [$task, $arguments[0]];
            $callableArgs = $arguments[1];           // execute $task->$arguments[0]($arguments[1])
        } else if (\is_callable($arguments)) {
            $callable = $arguments;                  // will execute $arguments()
        }   else if (\is_string($arguments) && \method_exists($task, $arguments)) {
            $callable = [$task, $arguments];         // will execute $task->$arguments()
        }

        if (isset($callable)) {
            $arguments = $this->callCallable($callable, $callableArgs, $getterArgs);
        }

        return $arguments;
    }

    protected function setJobParameters(AbstractTask $task, array $taskData)
    {
        if (isset($taskData['setters'])) {
            foreach ($taskData['setters'] as $setter) {
                $argument = $setter[1];
                $method = $setter[0];
                $argument = $this->getSetJobParameterArguments($task, $argument);

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
                } elseif (\is_string($argument) && \method_exists($this, $argument)) {
                    $argument = $this->$argument();
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

    /**
     * Returns the job description.
     * MUST BE OVERRIDE IN CONCRETE METHOD.
     *
     * @return string
     */
    public function getDescription(): string
    {
        throw new \LogicException('You must must override "getDescription" method in concrete class');
    }

    public function isCancelled(): bool
    {
        $this->refresh('status');
        return $this->infoIsCancelled();
    }
}
