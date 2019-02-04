<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Info;

interface InfoInterface
{
    /**
     * @param string $prop
     * @return InfoInterface
     */
    public function refresh(string $prop = '');

    public function getId(): string;

    public function getName(): string;

    public function getDescription(): string;

    public function getMessage(): string;

    public function getCurrentStepNum(): int;

    public function getStepsNum(): int;

    public function getClass(): string;

    public function isRunning(): bool;

    public function isSuccessful(): bool;

    public function isError(): bool;

    public function isCancelled(): bool;

    public function asArray(): array;
}
