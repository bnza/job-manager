<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner;

use Bnza\JobManagerBundle\Exception\JobManagerException;

class Status
{
    const RUNNING = 0b001;
    const SUCCESS = 0b010;
    const ERROR = 0b100;

    private $status;

    public function __construct(int $status = 0b0000)
    {
        $this->status = $status;
    }

    public function get(): int
    {
        return $this->status;
    }

    /**
     * @return Status
     *
     * @throws JobManagerException
     */
    public function run(): Status
    {
        if (0b0000 != $this->status) {
            throw new JobManagerException("Only clean statuses can be ran [$this]");
        }
        $this->status |= self::RUNNING;

        return $this;
    }

    /**
     * @return Status
     *
     * @throws JobManagerException
     */
    public function end(): Status
    {
        if (!$this->isRunning()) {
            throw new JobManagerException('Cannot end a not running job');
        }
        $this->status &= ~self::RUNNING;

        return $this;
    }

    public function isRunning(): bool
    {
        return (bool) ($this->status & self::RUNNING);
    }

    public function __toString()
    {
        return (string) $this->status;
    }

    public function error(): Status
    {
        $this->status |= self::ERROR;
        $this->status &= ~self::RUNNING;

        return $this;
    }

    public function isError(): bool
    {
        return (bool) ($this->status & self::ERROR);
    }

    public function success(): Status
    {
        $this->status |= self::SUCCESS;
        $this->status &= ~self::RUNNING;

        return $this;
    }

    public function isSuccessful(): bool
    {
        return (bool) ($this->status & self::SUCCESS);
    }
}
