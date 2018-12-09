<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Job\JobInfo;
use Bnza\JobManagerBundle\Job\TaskInfo;
use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;

class JobInfoTest extends \PHPUnit\Framework\TestCase
{
    private function getObjectManagerMock(RunnableEntityInterface $entity)
    {
        $om = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $om
            ->method('find')
            ->willReturn($entity);

        return $om;
    }

    public function testConstructor()
    {
        $entity = new JobEntity();

        $entity->getStatus()->run();

        $om = $this->getObjectManagerMock($entity);

        $info = new JobInfo($om, get_class($entity), $entity->getId());

        $this->assertTrue($info->isRunning());

        $this->assertFalse($info->isError());

        return $info;
    }

    /**
     * @depends testConstructor
     */
    public function testCancelSetStatus(JobInfo $info)
    {
        $info->cancel();

        $this->assertFalse($info->isRunning());

        $this->assertTrue($info->isError());

        return $info;
    }

    /**
     * @depends testCancelSetStatus
     */
    public function testCancel(JobInfo $info)
    {
        $e = new JobManagerCancelledJobException();

        $this->assertRegExp("/{$e->getMessage()}/", $info->getError());
    }

    public function testCancelPersistsEntity()
    {
        $entity = new JobEntity();

        $om = $this->getObjectManagerMock($entity);

        $om->expects($spy = $this->once())
            ->method('persist');

        $info = new JobInfo($om, $entity);

        $info->cancel();

        $persist = $spy->getInvocations()[0];

        $this->assertEquals($entity, $persist->getParameters()[0]);
    }

    public function testIsSuccessful()
    {
        $entity = new JobEntity();

        $entity->getStatus()->success();

        $om = $this->getObjectManagerMock($entity);

        $info = new JobInfo($om, get_class($entity), $entity->getId());

        $this->assertFalse($info->isRunning());

        $this->assertTrue($info->isSuccessful());
    }

    public function testGetTask()
    {
        $entity = new JobEntity();

        $task = new TaskEntity('', (int) rand(0, 100));

        $entity->addTask($task);

        $om = $this->getObjectManagerMock($entity);

        $info = new JobInfo($om, $entity);

        $this->assertInstanceOf(TaskInfo::class, $info->getTask($task->getNum()));
    }
}
