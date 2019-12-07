<?php

namespace Bnza\JobManagerBundle\Entity;

use Bnza\JobManagerBundle\Status\Status;

class TaskEntity implements TaskEntityInterface
{
    use TaskInfoEntityTrait;

    public function setStartedAt(float $starteAt): TaskEntityInterface
    {
        $this->startedAt = $starteAt;
        return $this;
    }

    public function setFinishedAt(float $finishedAt): TaskEntityInterface
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    public function setDescription(string $description): TaskEntityInterface
    {
        $this->description = $description;
        return $this;
    }

    public function setClass(string $class): TaskEntityInterface
    {
        $this->class = $class;
        return $this;
    }

    public function setUuid(string $uuid): TaskEntityInterface
    {
        $this->uuid = $uuid;
        return $this;
    }

    public function setStatus(Status $status): TaskEntityInterface
    {
        $this->status = $status;
        return $this;
    }
}
