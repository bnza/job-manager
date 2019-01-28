<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Info;


use Bnza\JobManagerBundle\Info\JobInfo;
use Bnza\JobManagerBundle\Info\TaskInfo;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;

class TaskInfoTest extends \PHPUnit\Framework\TestCase
{
    use MockUtilsTrait;

    public function testMethodGetJobWillReturnJobInfo()
    {
        $mockOm = $this->getMockObjectManager();
        $jobEntity = $this->getMockJobEntity();
        $taskEntity = $this->getMockTaskEntity();


        $taskEntity->expects($this->once())->method('getJob')->willReturn($jobEntity);

        $mockTask = $this->getMockTask(TaskInfo::class, ['getObjectManager', 'getEntity']);
        $mockTask->method('getObjectManager')->willReturn($mockOm);
        $mockTask->method('getEntity')->willReturn($taskEntity);
        $this->assertInstanceOf(JobInfo::class, $mockTask->getJob());
    }
}
