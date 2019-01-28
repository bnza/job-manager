<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Fixture\Runner\Task;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\Runner\Task\AbstractTask;

class DummyTask1 extends AbstractTask
{
    public $prop1;
    public $prop2;

    public function __construct(ObjectManagerInterface $om, JobInterface $job, int $num, string $param1, int $param2 = 2)
    {
        parent::__construct($om, $job, $num);
        $this->prop1 = $param1;
        $this->prop2 = $param2;
    }

    public function getDefaultDescription(): string
    {
        return 'DummyTask description';
    }

    public function getName(): string
    {
        return 'DummyTask name';
    }

    public function getSteps(): iterable
    {
        return [
            [$this, 'runMethod'], ['arg0', 'arg1'],
        ];
    }

    public function run(): void
    {
    }

    function executeStep(array $arguments): void
    {
        // TODO: Implement executeStep() method.
    }
}
