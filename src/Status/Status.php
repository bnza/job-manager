<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Status;

class Status extends StatusInfo implements StatusInterface
{
    public function setRunning(): StatusInterface
    {
        $this->status |= self::RUNNING;
        return $this;
    }

    public function setSkipped(): StatusInterface
    {
        $this->status |= self::SKIPPED;
        return $this;
    }

    public function setSuccess(): StatusInterface
    {
        $this->status |= self::SUCCESS;
        $this->status &= ~self::RUNNING;
        return $this;
    }

    public function setError(): StatusInterface
    {
        $this->status |= self::ERROR;
        $this->status &= ~self::RUNNING;
        return $this;
    }

    public function setCancelled(): StatusInterface
    {
        $this->status |= self::CANCELLED;
        return $this;
    }
}
