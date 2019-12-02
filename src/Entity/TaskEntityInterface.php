<?php

namespace Bnza\JobManagerBundle\Entity;

use Bnza\JobManagerBundle\Status\Status;

interface TaskEntityInterface extends TaskInfoEntityInterface
{
    public function setStartedAt(float $starteAt): self;

    public function setFinishedAt(float $finishedAt): self;

    public function setDescription(string $description): self;

    public function setClass(string $class): self;

    public function setId(string $id): self;

    public function setStatus(Status $status): self;
}
