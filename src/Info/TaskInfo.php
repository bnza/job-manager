<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Info;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

class TaskInfo implements TaskInfoInterface
{
    use InfoTrait;

    /**
     * @var TaskEntityInterface
     */
    protected $entity;

    public function __construct(ObjectManagerInterface $om, $entity, $jobId = '', $taskNum = -1)
    {
        $this->setUpRunnableInfo($om, $entity, $jobId, $taskNum);
    }

    /**
     * @return TaskEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function getNum(): int
    {
        return $this->getEntity()->getNum();
    }
}
