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

class DummyJob1 extends AbstractJob
{
    public function getName(): string
    {
        return 'bnza:job:test:dummy-1';
    }

    public function getDescription(): string
    {
        return 'Dummy job description';
    }

    public function getSteps(): iterable
    {
        return [
            ['class' => DummyTask2::class],
            ['class' => DummyTask2::class]
        ];
    }
}
