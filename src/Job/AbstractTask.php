<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Job;


use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;

abstract class AbstractTask extends AbstractRunnable
{
    public function __construct(ObjectManagerInterface $om, JobEntityInterface $jobEntity, int $num)
    {
        $entity = new TaskEntity($jobEntity, $num);
        parent::__construct($om, $entity);
    }

    public function getNum(): int
    {
        return $this->getEntity()->getNum();
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

    public function run(): void
    {
        $this->configure();
        foreach ($this->getSteps() as $step) {
            call_user_func_array($step[0], $step[1]);
            $this->next();
        }
        $this->terminate();
    }

    /**
     * Rollback function, just an empty placeholder.
     * MUST be implemented when needed
     */
    public function rollback(): void
    {}
}
