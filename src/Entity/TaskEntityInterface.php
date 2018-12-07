<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
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
