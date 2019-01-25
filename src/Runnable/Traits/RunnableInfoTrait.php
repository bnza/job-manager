<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable\Traits;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;

trait RunnableInfoTrait
{
    /**
     * @var ObjectManagerInterface;
     */
    protected $om;

    /**
     * @var RunnableEntityInterface
     */
    protected $entity;

//    /**
//     * RunnableInfoTrait constructor.
//     *
//     * @param ObjectManagerInterface $om
//     * @param string                 $class
//     * @param $jobId
//     * @param int $taskNum
//     *
//     * @throws JobManagerEntityNotFoundException
//     */
//    public function __construct(ObjectManagerInterface $om, string $class = '', $jobId = '', $taskNum = -1)
//    {
//        $this->om = $om;
//
//        if (!$this->entity) {
//            if (!$class) {
//                throw new \InvalidArgumentException('Entity class must be set');
//            }
//            $this->entity = $this->getObjectManager()->find($class, $jobId, $taskNum);
//        }
//    }

    /**
     * @return RunnableEntityInterface
     */
    protected function getEntity()
    {
        return $this->entity;
    }

    protected function setEntity($entity, string $class = '', $jobId = '', $taskNum = -1): self
    {
        if ($entity instanceof RunnableEntityInterface) {
            $this->entity = $entity;
        } else {
            $this->entity = $this->getObjectManager()->find($class, $jobId, $taskNum);
        }
        return $this;
    }

    protected function getObjectManager(): ObjectManagerInterface
    {
        return $this->om;
    }

    protected function setObjectManager(ObjectManagerInterface $om): self
    {
        $this->om = $om;
        return $this;
    }

    protected function setUpRunnableInfo(ObjectManagerInterface $om, $entity, $jobId = '', $taskNum = -1): self
    {
        return $this
            ->setObjectManager($om)
            ->setEntity($entity, $jobId, $taskNum);
    }

    /**
     * @param string $prop
     *
     * @return self
     *
     * @throws \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     */
    public function refresh(string $prop = ''): self
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
