<?php

namespace Bnza\JobManagerBundle\Status;

class StatusInfo implements StatusInfoInterface
{
    const CLEAN = 0;
    const RUNNING = 0b1;
    const SKIPPED = 0b10;
    const SUCCESS = 0b100;
    const ERROR = 0b1000;
    const CANCELLED = 0b10000;

    /**
     * @var int
     */
    protected $status;

    public function __construct(int $status = self::CLEAN)
    {
        $this->status = $status;
    }

    public function isClean(): bool
    {
        return $this->is(self::CLEAN);
    }
    
    public function isRunning(): bool
    {
        return $this->is(self::RUNNING);
    }
    
    public function isSkipped(): bool
    {
        return $this->is(self::SKIPPED);
    }

    public function isSuccess(): bool
    {
        return $this->is(self::SUCCESS);
    }

    public function isCancelled(): bool
    {
        return $this->is(self::CANCELLED);
    }

    public function isError(): bool
    {
        return $this->is(self::ERROR);
    }

    /**
     * @param int $status Integer status value to be checked against
     */
    public function is(int $status): bool
    {
        if ($status === self::CLEAN) {
            return $this->status === $status;
        }
        return (bool) ($this->status & $status);
    }

    public function __toString(): string
    {
        return (string) $this->status;
    }
}
