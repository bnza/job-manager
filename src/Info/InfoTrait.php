<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Info;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;
use Doctrine\Common\Inflector\Inflector;

trait InfoTrait
{
    /**
     * @var ObjectManagerInterface;
     */
    protected $om;

    /**
     * @var RunnableEntityInterface
     */
    protected $entity;

    /**
     * @return RunnableEntityInterface
     */
    protected function getEntity()
    {
        return $this->entity;
    }

    protected function setEntity($entity,  $jobId = '', $taskNum = -1): self
    {
        if ($entity instanceof RunnableEntityInterface) {
            $this->entity = $entity;
        } else {
            $this->entity = $this->getObjectManager()->find($entity, $jobId, $taskNum);
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

    public function getDescription(): string
    {
        return $this->getEntity()->getDescription();
    }

    public function getMessage(): string
    {
        return $this->getEntity()->getMessage();
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

    public function isCancelled(): bool
    {
        return $this->getEntity()->getStatus()->isCancelled();
    }

    public function asArray(): array
    {
        $array = [];
        foreach (['name', 'class', 'description', 'steps_num'] as $key) {
            $array[$key] = $this->{'get'.Inflector::classify($key)}();
        }
        $array['status'] = $this->getEntity()->getStatus()->get();
        return $array;
    }
}
