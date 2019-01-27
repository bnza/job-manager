<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Task;


use Bnza\JobManagerBundle\Tests\MockUtilsTrait;

trait CommonTaskTestTrait
{
    use MockUtilsTrait;

    abstract protected function getClassName(): string;

    abstract protected function getTaskName(): string;

    public function testGetterGetNameWillReturnExpectedValue()
    {
        $mockTask = $this->getMockTask($this->getClassName());
        $this->assertEquals($this->getTaskName(), $mockTask->getName());
    }

    public function testGetterGetDescriptionWillReturnDefaultDescription()
    {
        $mockTask = $this->getMockTask($this->getClassName());
        $this->assertEquals($mockTask->getDefaultDescription(), $mockTask->getDescription());
    }

}
