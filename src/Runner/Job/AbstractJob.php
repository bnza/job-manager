<?php
/**
 * Copyright (c) 2019.
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
use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runner\RunnableTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Return an iterable which contains an ordered list of data used for instantiate, set up, execute an AbstractTask's
     * subclass instance.
     * array(3) {
     *  [class]=>string(*) The fully qualified Task class name (MUST implements TaskInterface) (required)
     *  [condition]=>array|string Determines if task will be run (optional)
     *  [negateCondition]=>mixed Determines if condition will be negated (optional)
     *  [arguments]=>array The Task* subclass arguments, the AbstractTask ones are provided by the function (optional)
     *  [parameters]=>array Call the provided Task* setter methods with the relative Job* getter methods. It will called before Task*::configure() (optional)
     *  [setters]=>string Call the provided Job* setter methods with the relative Task* getter methods. It will called after Task*::terminate() (optional)
     *}.
     *
     * e.g.
     *  [
     *      'class' => DummyTask,
     *      'condition' => ['getSomeValue'],
     *      'negateCondition' => true
     *      'arguments' => [ 'some value', $this->getArgument2 ],
     *      'parameters' => [
     *          ['setTaskParameter1', 'some parameter'],
     *          ['setTaskParameter1', 'getJobParameter1']
     *      ],
     *      'setters' => [
     *          ['setJobParameter1', 'some parameter'],
     *          ['setJobParameter1', 'getTaskParameter1']
     *      ]
     *  ]
     *
     * @see AbstractJob::run()
     * @see AbstractJob::runTask()
     *
     * @return iterable
     */
    abstract public function getSteps(): iterable;

    public function __construct(ObjectManagerInterface $om, EventDispatcherInterface $dispatcher, $entity = '', array $parameters = [])
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
        $this->getEntity()->setStepsNum($this->getStepsNum());
        $this->persist();
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher(): EventDispatcherInterface
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

    /**
     * Refresh job status and check for CANCELLED flag.
     *
     * @return bool
     *
     * @throws \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     */
    public function isCancelled(): bool
    {
        $this->refresh('status');

        return $this->infoIsCancelled();
    }

    /**
     * Runs the job executing the provided steps.
     *
     * @see AbstractJob::getSteps()
     * @see AbstractJob::runTask()
     *
     * @throws \Throwable
     */
    final public function run(): void
    {
        $this->running();
        try {
            $this->configure();
            foreach ($this->getSteps() as $num => $taskData) {
                if ($this->isCancelled()) {
                    throw new JobManagerCancelledJobException();
                }
                if (!$this->checkTaskRunConditions($taskData)) {
                    continue;
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

    /**
     * Rollbacks tasks.
     */
    public function rollback(): void
    {
        foreach ($this->getTasks() as $num => $task) {
            $task->rollback();
        }
    }

    /**
     * Set the error flag status and rollback job.
     *
     * @param \Throwable $e
     */
    public function error(\Throwable $e): void
    {
        $this->persistError($e);
        try {
            $this->rollback();
        } catch (\Throwable $t) {
            $this->handleRollbackError($t);
        }
        $this->getDispatcher()->dispatch(JobEndedEvent::NAME, new JobEndedEvent($this));
    }

    protected function handleRollbackError(\Throwable $t)
    {
        //TODO handle rollback's errors
    }

    /**
     * Persist error relate data to ObjectManager.
     *
     * @param \Throwable $e
     */
    protected function persistError(\Throwable $e): void
    {
        $this->getEntity()->setError($e);
        $this->getEntity()->getStatus()->error();
        $this->persist();
    }

    /**
     * Set the success flag status.
     */
    public function success(): void
    {
        $this->getEntity()->getStatus()->success();
        $this->persist('status');
        $this->getDispatcher()->dispatch(JobEndedEvent::NAME, new JobEndedEvent($this));
    }

    /**
     * Set the run flag status.
     */
    protected function running(): void
    {
        $this->getEntity()->getStatus()->run();
        $this->persist('status');
        $this->getDispatcher()->dispatch(JobStartedEvent::NAME, new JobStartedEvent($this));
    }

    /**
     * Initializes a new TaskInterface instance using $taskData provided by the getSteps() method.
     *
     * @see AbstractJob::getSteps()
     *
     * @param int   $num
     * @param array $taskData
     *
     * @return AbstractTask
     */
    protected function initTask(int $num, array $taskData): AbstractTask
    {
        if (!isset($taskData['class'])) {
            throw new \InvalidArgumentException('Task class must be provided');
        }

        $class = $taskData['class'];

        if (!\is_string($class)) {
            throw new \InvalidArgumentException('Task class must be a string');
        }

        if (in_array(AbstractTask::class, \class_parents($class))) {
            $this->setCurrentStepNum($num);
            $task = $this->createTask($class, $num, $taskData);
            $this->tasks[$num] = $task;

            return $task;
        }
        throw new \InvalidArgumentException("Task class must implement TaskInterface: \"$class\" does not");
    }

    /**
     * This is just a method stub. Must be implemented in subclasses when needed.
     *
     * @param array $parameters
     *
     * @throws \InvalidArgumentException
     */
    protected function checkConstructorParameters(array $parameters)
    {
    }

    /**
     * @param array $parameters
     *
     * @throws \InvalidArgumentException
     */
    protected function setParameterBag(array $parameters)
    {
        $this->checkConstructorParameters($parameters);
        $this->parameters = new ParameterBag($parameters);
    }

    /**
     * Returns an arguments' array used by any AbstractTask subclass constructor.
     *
     * @param $taskData
     *
     * @return array
     */
    protected function getTaskConstructorArguments($taskData)
    {
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

        return $arguments;
    }

    /**
     * Calls the provided callable and returns value. If $args is also a callable use the return value as $param_arr of
     * call_user_func_array() function.
     *
     * @param callable $callable   Any valid callable
     * @param array    $args       The $callable arguments
     * @param array    $getterArgs This value is used as callable arguments if $args is a callable
     *
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
     * Retrieves the arguments used by Abstract::setJobParameters().
     *
     *  [
     *      ['setJobParameter1', 'some parameter'],      // hardcoded value      --> $this->setJobParameter1('some parameter')
     *      ['setJobParameter1', 'sys_get_temp_dir'],    // callable value       --> $this->setJobParameter1(sys_get_temp_dir())
     *      ['setJobParameter1', 'getTaskParameter1'],   // task getter value    --> $this->setJobParameter1($task->getTaskParameter1())
     *      ['setJobParameter1',
     *          [
     *              'getTaskParameter1',
     *              'sys_get_temp_dir'
     *          ]
     *      ],                                           // task getter value with args --> $this->setJobParameter1($task->getTaskParameter1(sys_get_temp_dir()))
     *      ['setJobParameter1',
     *          [
     *              [$this->someObject, 'getter'],
     *              [$this, 'getJobParameter2']         // task getter value with args --> $this->setJobParameter1($this->someObject->getter($this->getJobParameter2()))
     *          ]
     *      ],
     *      ['setJobParameter1',
     *          [
     *              'getTaskParameter1',
     *              [$this, 'getJobParameter2']         // task getter value with args --> $this->setJobParameter1($task->getTaskParameter1($this->getJobParameter2()))
     *          ]
     *      ],
     *  ]
     *
     * @see AbstractJob::setJobParameters()
     *
     * @param AbstractTask $task
     * @param $arguments
     *
     * @return mixed The arguments used by Abstract::setJobParameters() internal function call
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
        } elseif (
            \is_array($arguments)
            && isset($arguments[0])
            && \is_string($arguments[0])
            && \method_exists($task, $arguments[0])
        ) {
            $callable = [$task, $arguments[0]];
            $callableArgs = $arguments[1];           // execute $task->$arguments[0]($arguments[1])
        } elseif (\is_callable($arguments)) {
            $callable = $arguments;                  // will execute $arguments()
        } elseif (\is_string($arguments) && \method_exists($task, $arguments)) {
            $callable = [$task, $arguments];         // will execute $task->$arguments()
        }

        if (isset($callable)) {
            $arguments = $this->callCallable($callable, $callableArgs, $getterArgs);
        }

        return $arguments;
    }

    /**
     * This method is called after each tasks run to set job's properties for further tasks.
     *
     * @see AbstractJob::getSteps()
     * @see AbstractJob::runTask()
     *
     * @param AbstractTask $task     The task which holds needed properties
     * @param array        $taskData The data use by task for running
     */
    protected function setJobParameters(AbstractTask $task, array $taskData)
    {
        if (isset($taskData['setters'])) {
            foreach ($taskData['setters'] as $setter) {
                $argument = $setter[1];
                $argument = $this->getSetJobParameterArguments($task, $argument);

                $method = $setter[0];
                if (\is_string($method)) {
                    if (!\method_exists($this, $method)) {
                        throw new \InvalidArgumentException(sprintf('No "%s" method found in %s', $method, self::class));
                    }
                    $this->$method($argument);
                } else {
                    throw new \InvalidArgumentException('Invalid setter method provided: '.gettype($method));
                }
            }
        }
    }

    /**
     * This method is called before each tasks run to set task's needed properties
     * It uses the values contained in "parameters" key $taskData array (if exists).
     *
     *
     * [
            ...
     *      'parameters' => [
     *          ['setTaskParameter1', 'some parameter'],    // hardcoded value      --> $this->setTaskParameter1('some parameter')
     *          ['setTaskParameter1', 'sys_get_temp_dir']   // callable             --> $this->setTaskParameter1(sys_get_temp_dir())
     *          ['setTaskParameter1', 'getJobParameter1']   // task getter value    --> $this->setTaskParameter1($this->getJobParameter1())
     *      ],
            ...
     *  ]
     *
     * @see AbstractJob::getSteps()
     * @see AbstractJob::runTask()
     *
     * @param AbstractTask $task     The task which will be run
     * @param array        $taskData The data use by task for running
     */
    protected function setTaskParameters(AbstractTask $task, array $taskData)
    {
        if (isset($taskData['parameters'])) {
            foreach ($taskData['parameters'] as $parameter) {
                if (\is_array($parameter)) {
                    $argument = $parameter[1];
                    $method = $parameter[0];
                    if (\is_callable($argument)) {
                        $argument = $argument();    //callable return value
                    } elseif (\is_string($argument) && \method_exists($this, $argument)) {
                        $argument = $this->$argument(); //job method return value
                    }

                    if (\is_string($method)) {
                        if (!\method_exists($task, $method)) {
                            throw new \InvalidArgumentException(sprintf('No "%s" method found in %s', $method, \get_class($task)));
                        }
                        $task->$method($argument);
                    } else {
                        throw new \InvalidArgumentException('Invalid task parameter setter provided: '.gettype($method));
                    }
                } else {
                    throw new \InvalidArgumentException('Task setter must be an array');
                }
            }
        }
    }

    /**
     * Initializes and return an AbstractTask instance using the provided task data.
     *
     * @see AbstractTask::getSteps()
     *
     * @param string $class    The fully qualified taskData classname
     * @param int    $num      The job's task number
     * @param array  $taskData The data use by task for running
     *
     * @return AbstractTask
     */
    protected function createTask(string $class, int $num, array $taskData): AbstractTask
    {
        $arguments = $this->getTaskConstructorArguments($taskData);

        return new $class($this->getObjectManager(), $this, $num, ...$arguments);
    }

    /**
     * Run the nth job's step using the provided task data.
     *
     * @see AbstractJob::getSteps()
     *
     * @param int   $num      The current task/step index
     * @param array $taskData The data use by task for running
     *
     * @throws JobManagerCancelledJobException
     */
    protected function runTask(int $num, array $taskData)
    {
        $task = $this->initTask($num, $taskData);
        $this->setTaskParameters($task, $taskData);
        $task->run();
        $this->setJobParameters($task, $taskData);
    }

    /**
     * Determines if the the task will be run.
     *
     * @param array $taskData
     *
     * @return bool
     */
    protected function checkTaskRunConditions(array $taskData): bool
    {
        $run = true;

        if (isset($taskData['negateCondition'])) {
            $modifier = (bool) $taskData['negateCondition'];
        } else {
            $modifier = false;
        }

        if (isset($taskData['condition'])) {
            $condition = $taskData['condition'];

            if (!\is_bool($condition) && !$condition) {
                throw new \InvalidArgumentException('Not boolean falsy values are not allowed');
            } elseif (\is_bool($condition)) {
                $run = $condition;
            } elseif (\is_callable($condition)) {
                $run = $condition();
            } elseif (\is_array($condition)) {
                $method = $condition[0];
                $argument = isset($condition[1]) ? $condition[1] : [];
                if (\is_callable($method)) {
                    if (!\is_array($argument)) {
                        $argument = [$argument];
                    }
                    $run = \call_user_func_array($method, $argument);
                } elseif (\is_string($method) && \method_exists($this, $method)) {
                    if (!\is_array($argument)) {
                        $argument = [$argument];
                    }
                    $run = \call_user_func_array([$this, $method], $argument);
                } else {
                    throw new \InvalidArgumentException('Invalid condition method provided: '.gettype($condition));
                }
            } elseif (\is_string($condition) && \method_exists($this, $condition)) {
                $run = $this->$condition();
            } else {
                throw new \InvalidArgumentException('Invalid condition provided: '.gettype($condition));
            }
        }

        return (bool) ($modifier ? !$run : $run);
    }
}
