<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Fixture\Runner\Task;

use Bnza\JobManagerBundle\Runner\Task\AbstractTask;

class DummyTask2 extends AbstractTask
{

    public function getName(): string
    {
        return 'bnza:task:test:dummy-2';
    }

    function getDefaultDescription(): string
    {
        return 'Dummy Task 2 description';
    }

    public function getSteps(): iterable
    {
        return [
          ['arg1'],[ 'arg2']
        ];
    }

    public function executeStep(array $arguments): void
    {}
}
