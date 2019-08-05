<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Fixture\Runner\Job;


use Bnza\JobManagerBundle\Runner\Job\AbstractJob;
use Bnza\JobManagerBundle\Tests\Fixture\Runner\Task\DummyTask2;

class DummyJob2 extends AbstractJob
{
    private $someParameter;
    private $someOtherParameter;

    public function getName(): string
    {
        return 'bnza:job:test:dummy-2';
    }

    public function getDescription(): string
    {
        return 'Dummy job description with parameters';
    }

    public function getSteps(): iterable
    {
        return [
            ['class' => DummyTask2::class],
            ['class' => DummyTask2::class]
        ];
    }

    public function setSomeParameter($param)
    {
        $this->someParameter = $param;
    }

    public function getSomeParameter()
    {
        return $this->someParameter;
    }

    public function setSomeOtherParameter($param)
    {
        $this->someOtherParameter = $param;
    }

    public function getSomeOtherParameter()
    {
        return $this->someOtherParameter;
    }

}
