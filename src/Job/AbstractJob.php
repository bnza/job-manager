<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */


namespace Bnza\JobManagerBundle\Job;


use Bnza\JobManagerBundle\Entity\JobEntityInterface;

abstract class AbstractJob extends AbstractRunnable
{

    /**
     * Registered and instantiated tasks. Used il rollback operations
     * @var AbstractTask[]
     */
    protected $tasks = [];

    /**
     * @var JobEntityInterface
     */
    protected $entity;

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
        $entity = $this->getEntity();

        $entity->getStatus()->run();
        $this->persist('status');

        $tasks = $this->getSteps();

        try {
            foreach ($tasks as $num => $taskData) {
                $task = $this->initTask($num, $taskData);
                $task->run();
            }
        } catch (\Throwable $e) {
            $this->error($e);
            $this->rollback();
            throw $e;
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
     *  [0]=>
     *  string(*) The fully qualified Task class name (MUST implements TaskInterface)
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
            $this->setCurrentStepNum($num);
            $task = new $class($this->getObjectManager(), $this->getEntity(), $num);
            $this->tasks[$num] = $task;

            return $task;
        }
        throw new \InvalidArgumentException("Task class must implement TaskInterface: \"$class\" does not");
    }
}
