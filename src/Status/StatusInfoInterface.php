<?php

namespace Bnza\JobManagerBundle\Status;

interface StatusInfoInterface
{
    public function is(int $status): bool;

    /**
     * @return string The integer status value string
     */
    public function __toString(): string;

    public function isClean(): bool;

    public function isRunning(): bool;
    
    public function isSkipped(): bool;

    public function isSuccess(): bool;

    public function isCancelled(): bool;
}
