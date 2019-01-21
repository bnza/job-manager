<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

class JobInfo extends AbstractRunnableInfo implements JobInfoInterface
{
    use Traits\Job\JobInfoTrait;

    /**
     * @var TaskInfo[]
     */
    protected $tasks = [];

    /**
     * @var JobEntityInterface
     */
    protected $entity;

    /**
     * @return JobEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function getError()
    {
        return $this->getEntity()->getError();
    }

    public function __construct(ObjectManagerInterface $om, $entity, $jobId = '')
    {
        if ($entity instanceof JobEntityInterface) {
            $this->entity = $entity;
            parent::__construct($om);
        } elseif (is_string($entity)) {
            parent::__construct($om, $entity, $jobId);
        }
    }

    /**
     * Set the job status to CANCELLED.
     */
    public function cancel()
    {
        $this
            ->getEntity()
            ->setError(new JobManagerCancelledJobException())
            ->getStatus()
            ->error();

        $this->getObjectManager()->persist($this->getEntity());
    }
}
