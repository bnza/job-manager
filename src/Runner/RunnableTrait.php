<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Info\InfoTrait;

trait RunnableTrait
{
    use InfoTrait;

    /**
     * @var int
     */
    protected $stepsNum = 0;

    abstract public function getSteps(): iterable;

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

    protected function setUpRunnable(ObjectManagerInterface $om, $entity, string $jobId = '', int $taskNum = -1): self
    {
        return $this
            ->setUpRunnableInfo($om, $entity, $jobId, $taskNum)
            ->updateEntity();
    }

    /**
     * Persist the entity.
     *
     * @param string $prop
     *
     * @return self
     */
    public function persist(string $prop = ''): self
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
        throw new \LogicException('You must must override "getName" method in concrete class');
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
     */
    protected function updateEntity(): self
    {
        $entity = $this->getEntity();

        $entity->setClass($this->getClass());
        $entity->setName($this->getName());
        $entity->setDescription($this->getDescription());
        //$entity->setStepsNum($this->getStepsNum());

        return $this;
    }

    protected function setCurrentStepNum(int $num)
    {
        $entity = $this->getEntity();

        $entity->setCurrentStepNum($num);

        $this->persist('current_step_num');
    }

    protected function setMessage(string $message)
    {
        $entity = $this->getEntity();

        $entity->setMessage($message);

        $this->persist('message');
    }

    protected function next()
    {
        $num = $this->getEntity()->getCurrentStepNum() + 1;

        $this->setCurrentStepNum($num);
    }

    /**
     * Setup function, just an empty placeholder.
     * MUST be implemented when needed
     */
    protected function configure(): void
    {}

    /**
     * Teardown function, just an empty placeholder.
     * MUST be implemented when needed
     */
    protected function terminate(): void
    {}
}
