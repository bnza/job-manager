<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Task;

use Bnza\JobManagerBundle\Job\AbstractRunnableInfo;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

class TaskInfo extends AbstractRunnableInfo implements TaskInfoInterface
{
    /**
     * @var TaskEntityInterface
     */
    protected $entity;

    public function __construct(ObjectManagerInterface $om, $entity, $jobId = '', $taskNum = -1)
    {
        if ($entity instanceof TaskEntityInterface) {
            $this->entity = $entity;
            parent::__construct($om);
        } elseif (is_string($entity)) {
            parent::__construct($om, $entity, $jobId, $taskNum);
        }
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
