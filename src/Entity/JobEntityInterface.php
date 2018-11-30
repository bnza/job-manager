<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Entity;

interface JobEntityInterface extends JobManagerEntityInterface
{
    public function getId(): string;

    public function getStatus(): int;

    public function setStatus($status): JobEntityInterface;

    public function setName(string $name): JobEntityInterface;

    public function setClass(string $class): JobEntityInterface;

    public function setStepsNum($num): JobEntityInterface;

    public function setCurrentStepNum($num): JobEntityInterface;

    public function addTask(TaskEntityInterface $task): JobEntityInterface;

    public function getTasks(): \ArrayIterator;

    public function getTask(int $num): TaskEntityInterface;

    public function clearTasks(): JobEntityInterface;

    public function getError(): string;

    public function setError(string $class): JobEntityInterface;
}
