<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;

class JobEntity extends AbstractJobManagerEntity implements JobEntityInterface
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var int
     */
    protected $status = 0;

    /**
     * @var int
     */
    protected $tasksNum = -1;

    /**
     * @var int
     */
    protected $currentTaskNum = -1;

    /**
     * @var \ArrayIterator
     */
    protected $tasks;

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
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus($status): JobEntityInterface
    {
        $this->status = (int) $status;

        return $this;
    }

    public function getCurrentTaskNum(): int
    {
        return $this->currentTaskNum;
    }

    public function setCurrentTaskNum($num): JobEntityInterface
    {
        $this->currentTaskNum = (int) $num;

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

    public function getTasksNum(): int
    {
        return $this->tasksNum;
    }

    public function setTasksNum($num): JobEntityInterface
    {
        $this->tasksNum = (int) $num;

        return $this;
    }
}
