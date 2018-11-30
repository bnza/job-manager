<?php
/**
 * (c) 2018 - Pietro Baldassarri <pietro.baldassarri@gmail.com>.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bnza\JobManagerBundle\Entity;

interface TaskEntityInterface extends JobManagerEntityInterface
{
    public function setName(string $name): TaskEntityInterface;

    public function setClass(string $class): TaskEntityInterface;

    public function getJob(): ?JobEntityInterface;

    public function setJob(JobEntityInterface $job): TaskEntityInterface;

    public function setNum($num): TaskEntityInterface;

    public function getNum(): int;

    public function setStepsNum($num): TaskEntityInterface;

    public function setCurrentStepNum($num): TaskEntityInterface;
}
