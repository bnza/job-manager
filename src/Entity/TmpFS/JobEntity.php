<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Runnable\Status;

class JobEntity extends AbstractRunnableEntity implements JobEntityInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $status;

    /**
     * @var \ArrayIterator
     */
    protected $tasks;

    /**
     * @var string
     */
    protected $error = '';

    /**
     * JobEntity constructor.
     *
     * @param string $id The job id (must be a SHA1 hash)
     */
    public function __construct(string $id = '')
    {
        if ($id) {
            if (ctype_xdigit($id) && 40 == strlen($id)) {
                $this->id = $id;
            } else {
                throw new \InvalidArgumentException(sprintf('"%s" is not a valid sha1 hash', $id));
            }
        } else {
            $this->id = sha1(microtime());
        }
        $this->tasks = new \ArrayIterator();
        $this->status = new Status();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setClass(string $class): RunnableEntityInterface
    {
        $this->class = $class;

        return $this;
    }

    public function setName(string $name): RunnableEntityInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus($status): RunnableEntityInterface
    {
        if ($status instanceof Status) {
            $this->status = $status;
        } else {
            $this->status = new Status((int) $status);
        }

        return $this;
    }

    public function setCurrentStepNum($num): RunnableEntityInterface
    {
        $this->currentStepNum = (int) $num;

        return $this;
    }

    public function addTask(TaskEntityInterface $task): JobEntityInterface
    {
        if ($this->tasks->offsetExists($task->getNum())) {
            throw new \LogicException('Cannot replace existing task');
        }
        $this->tasks->offsetSet($task->getNum(), $task);
        $task->setJob($this);

        return $this;
    }

    public function getTasks(): \ArrayIterator
    {
        return $this->tasks;
    }

    public function getTask(int $num): TaskEntityInterface
    {
        if ($this->tasks->offsetExists($num)) {
            return $this->tasks->offsetGet($num);
        }
        throw new \RuntimeException("No tasks at index $num");
    }

    public function clearTasks(): JobEntityInterface
    {
        $this->tasks = new \ArrayIterator();

        return $this;
    }

    public function setStepsNum($num): RunnableEntityInterface
    {
        $this->stepsNum = (int) $num;

        return $this;
    }

    public function getError(): string
    {
        return $this->error;
    }

    public function setError($error): RunnableEntityInterface
    {
        $this->error = (string) $error;

        return $this;
    }
}
