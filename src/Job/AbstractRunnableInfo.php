<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;

use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;

abstract class AbstractRunnableInfo implements RunnableInfoInterface
{
    /**
     * @var ObjectManager;
     */
    protected $om;

    /**
     * @var JobManagerEntityInterface
     */
    protected $entity;

    /**
     * AbstractRunnableInfo constructor.
     *
     * @param ObjectManager $om
     * @param string        $class
     * @param $jobId
     * @param int $taskNum
     *
     * @throws JobManagerEntityNotFoundException
     */
    public function __construct(ObjectManager $om, string $class = '', $jobId = '', $taskNum = -1)
    {
        $this->om = $om;

        if (!$this->entity) {
            if (!$class) {
                throw new \InvalidArgumentException('Entity class must be set');
            }
            $this->entity = $this->om->find($class, $jobId, $taskNum);
        }
    }

    /**
     * @return JobManagerEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    protected function getObjectManager(): ObjectManager
    {
        return $this->om;
    }

    /**
     * @param string $prop
     *
     * @return RunnableInfoInterface
     *
     * @throws \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     */
    public function refresh(string $prop = ''): RunnableInfoInterface
    {
        $this->getObjectManager()->refresh($this->getEntity(), $prop);

        return $this;
    }

    public function getId(): string
    {
        return $this->getEntity()->getId();
    }

    public function getName(): string
    {
        return $this->getEntity()->getName();
    }

    public function getClass(): string
    {
        return $this->getEntity()->getClass();
    }

    public function getCurrentStepNum(): int
    {
        return $this->getEntity()->getCurrentStepNum();
    }

    public function getStepsNum(): int
    {
        return $this->getEntity()->getStepsNum();
    }

    public function isRunning(): bool
    {
        return $this->getEntity()->getStatus()->isRunning();
    }

    public function isSuccessful(): bool
    {
        return $this->getEntity()->getStatus()->isSuccessful();
    }

    public function isError(): bool
    {
        return $this->getEntity()->getStatus()->isError();
    }
}
