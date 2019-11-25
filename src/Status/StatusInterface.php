<?php

namespace Bnza\JobManagerBundle\Status;

interface StatusInterface extends StatusInfoInterface
{
    public function setRunning(): self;

    public function setSkipped(): self;

    public function setError(): self;

    public function setCancelled(): self;
}
