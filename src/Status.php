<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle;

class Status
{
    const RUNNING = 0b1;
    const SKIPPED = 0b10;
    const SUCCESS = 0b100;
    const ERROR = 0b1000;
    const CANCELLED = 0b10000;

    /**
     * @var int
     */
    private $status;

    public function __construct(int $status = 0b0000)
    {
        $this->status = $status;
    }

    public function is(int $status): bool
    {
        return (bool) ($this->status & $status);
    }

    public function setRunning(): self
    {
        $this->status |= self::RUNNING;
        return $this;
    }

    public function isRunning(): bool
    {
        return $this->is(self::RUNNING);
    }

    public function setSkipped(): self
    {
        $this->status |= self::SKIPPED;
        return $this;
    }

    public function isSkipped(): bool
    {
        return $this->is(self::SKIPPED);
    }

    public function setSuccess(): self
    {
        $this->status |= self::SUCCESS;
        $this->status &= ~self::RUNNING;
        return $this;
    }

    public function isSuccess(): bool
    {
        return $this->is(self::SUCCESS);
    }

    public function setError(): self
    {
        $this->status |= self::ERROR;
        $this->status &= ~self::RUNNING;
        return $this;
    }

    public function isError(): bool
    {
        return $this->is(self::ERROR);
    }

    public function setCancelled(): self
    {
        $this->status |= self::CANCELLED;
        return $this;
    }

    public function isCancelled(): bool
    {
        return $this->is(self::CANCELLED);
    }


    public function __toString()
    {
        return (string) $this->status;
    }
}
