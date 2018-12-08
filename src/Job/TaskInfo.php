<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;

class TaskInfo extends AbstractInfoGetter
{
    /**
     * @var TaskEntityInterface
     */
    protected $entity;

    public function __construct(ObjectManager $om, $entity, $jobId = '', $taskNum = -1)
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

    public function getNum()
    {
        return $this->getEntity()->getNum();
    }
}
