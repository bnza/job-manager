<?php

namespace Bnza\JobManagerBundle\Entity;

interface TaskEntityFactoryInterface
{

  /**
   * Creates a new TaskEntityInterface instance using a associative array data
   *
   * @return TaskEntityInterface
   */
    public function create(array $data = []): TaskEntityInterface;
}
