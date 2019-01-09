<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

abstract class AbstractRunnable extends AbstractRunnableInfo implements RunnableInterface
{
    /**
     * @var int
     */
    protected $stepsNum = 0;

    /**
     * Counts the job/task's steps.
     *
     * @return int
     */
    protected function countStepsNum(): int
    {
        $steps = $this->getSteps();
        if (\is_array($steps)) {
            return \count($steps);
        } else {
            $count = 0;
            foreach ($steps as $task) {
                ++$count;
            }

            return $count;
        }
    }

    /**
     * AbstractRunnable constructor.
     *
     * @param ObjectManagerInterface  $om
     * @param RunnableEntityInterface $entity
     *
     * @throws \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     */
    public function __construct(ObjectManagerInterface $om, RunnableEntityInterface $entity)
    {
        $this->entity = $this->updateEntity($entity);
        parent::__construct($om);
        $this->persist();
    }

    /**
     * Persist the entity.
     *
     * @param string $prop
     *
     * @return RunnableInterface
     */
    public function persist(string $prop = ''): RunnableInterface
    {
        $this->getObjectManager()->persist($this->getEntity(), $prop);

        return $this;
    }

    /**
     * Gets the job/task's class.
     *
     * @return string
     */
    public function getClass(): string
    {
        return \get_class($this);
    }

    /**
     * Returns the job/task's name.
     * MUST BE OVERRIDE IN CONCRETE METHOD.
     *
     * @return string
     */
    public function getName(): string
    {
        throw new \LogicException('You must must override this method in concrete class');
    }

    /**
     * Returns the job/task's steps number.
     *
     * @return int
     */
    public function getStepsNum(): int
    {
        if (0 == $this->stepsNum) {
            $this->stepsNum = $this->countStepsNum();
        }

        return $this->stepsNum;
    }

    /**
     * Update the entity with the class specific data.
     *
     * @param RunnableEntityInterface $entity
     *
     * @return RunnableEntityInterface
     */
    protected function updateEntity(RunnableEntityInterface $entity): RunnableEntityInterface
    {
        $entity
            ->setClass($this->getClass())
            ->setName($this->getName())
            ->setStepsNum($this->getStepsNum());

        return $entity;
    }

    protected function setCurrentStepNum(int $num)
    {
        $entity = $this->getEntity();

        $entity->setCurrentStepNum($num);

        $this->persist('current_step_num');
    }

    protected function next()
    {
        $num = $this->getEntity()->getCurrentStepNum() + 1;

        $this->setCurrentStepNum($num);
    }

    public function error(\Throwable $e): void
    {
        $this->getEntity()->setError($e);
        $this->getEntity()->getStatus()->error();
        $this->persist();
    }

    public function success(): void
    {
        $this->getEntity()->getStatus()->success();
        $this->persist('status');
    }
}
