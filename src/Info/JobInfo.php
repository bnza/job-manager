<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Info;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

class JobInfo implements JobInfoInterface
{
    use InfoTrait;
    use JobInfoTrait;

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
        $this->setUpRunnableInfo($om, $entity, $jobId);
    }

    /**
     * Set the job status to CANCELLED.
     */
    public function cancel()
    {
        $this
            ->getEntity()
            ->getStatus()
            ->cancel();

        $this->getObjectManager()->persist($this->getEntity());
    }
}
