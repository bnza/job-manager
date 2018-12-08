<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;

interface RunnableInfoInterface
{
    public function refresh(string $prop = ''): RunnableInfoInterface;

    public function getId(): string;

    public function getName(): string;

    public function getCurrentStepNum(): int;

    public function getStepsNum(): int;

    public function getClass(): string;

    public function isRunning(): bool;

    public function isSuccessful(): bool;

    public function isError(): bool;
}