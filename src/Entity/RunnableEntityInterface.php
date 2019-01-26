<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity;

use Bnza\JobManagerBundle\Runner\Status;

interface RunnableEntityInterface
{
    public function getId(): string;

    public function getStatus(): Status;

    public function setStatus($status): self;

    public function getClass(): string;

    public function getName(): string;

    public function getCurrentStepNum(): int;

    public function getStepsNum(): int;

    public function getError(): string;

    public function setError($error): self;

    public function setName(string $name): self;

    public function setClass(string $class): self;

    public function setStepsNum($num): self;

    public function setCurrentStepNum($num): self;
}
