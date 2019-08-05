<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner;

use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Doctrine\Common\Inflector\Inflector;

class JobFactory
{
    use JobFactoryTrait;

    protected function setJobParameters(JobInterface $job, array $params)
    {
        foreach ($params as $prop => $value)
        {
            $method = 'set'.ucfirst(Inflector::camelize($prop));
            $job->$method($value);
        }
    }

    public function create(string $jobClass, $id = '', $params = []): JobInterface
    {
        $entity = new JobEntity($id);
        $job = new $jobClass($this->getObjectManager(), $this->getDispatcher(), $entity);
        $this->setJobParameters($job, $params);
        return $job;
    }
}
