<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Runnable\Status;

class JobEntityTest extends \PHPUnit\Framework\TestCase
{
    private $id = '6dcd4ce23d88e2ee9568ba546c007c63d9131c1b';

    public function testEmptyConstructor()
    {
        $entity = new JobEntity();
        $this->assertTrue(ctype_xdigit($entity->getId()));
        $this->assertEquals(40, strlen($entity->getId()));
    }

    public function testIdConstructor(): JobEntity
    {
        $entity = new JobEntity($this->id);
        $this->assertEquals($this->id, $entity->getId());

        return $entity;
    }

    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /".+" is not a valid sha1 hash/
     */
    public function testWrongIdConstructor()
    {
        $entity = new JobEntity('Not sha1 hash');
    }

    public function propertyProvider()
    {
        return [
            ['Class', self::class],
            ['Name', 'Runnable\\Task name'],
            ['Status', new Status(1)],
            ['CurrentStepNum', 2],
            ['StepsNum', 3],
            ['Error', 'Bad error'],
        ];
    }

    /**
     * @depends testIdConstructor
     * @dataProvider propertyProvider
     *
     * @param string $prop
     * @param $value
     * @param JobEntity $job
     */
    public function testSetGetProperties(string $prop, $value, JobEntity $job)
    {
        $job->{"set$prop"}($value);
        $this->assertEquals($value, $job->{"get$prop"}());
    }

    /**
     * @depends testIdConstructor
     *
     * @param JobEntity $job
     *
     * @return JobEntity
     *
     * @throws \ReflectionException
     */
    public function testAddTask(JobEntity $job)
    {
        $task = $this->getMockForAbstractClass(TaskEntityInterface::class);
        $task
            ->method('getNum')
            ->willReturn(1);
        $this->assertCount(0, $job->getTasks());
        $job->addTask($task);
        $this->assertCount(1, $job->getTasks());

        return $job;
    }

    /**
     * @depends testAddTask
     *
     * @param JobEntity $job
     *
     * @return JobEntity
     */
    public function testGetTask(JobEntity $job)
    {
        $task = $job->getTask(1);
        $this->assertInstanceOf(TaskEntityInterface::class, $task);

        return $job;
    }

    /**
     * @depends testGetTask
     * @expectedException              \LogicException
     * @expectedExceptionMessage Cannot replace existing task
     *
     * @param JobEntity $job
     */
    public function testAddTaskThrowsExceptionOnDuplicateTaskNum(JobEntity $job)
    {
        $task = $job->getTask(1);
        $job->addTask($task);
    }

    /**
     * @depends testAddTask
     * @expectedException              \RuntimeException
     * @expectedExceptionMessageRegExp /No tasks at index \d+/
     *
     * @param JobEntity $job
     */
    public function testGetTaskThrowsExceptionOnWrongIndex(JobEntity $job)
    {
        $task = $job->getTask(2);
    }

    /**
     * @depends testAddTask
     *
     * @param JobEntity $job
     */
    public function testClearTask(JobEntity $job)
    {
        $job->clearTasks();
        $this->assertCount(0, $job->getTasks());
    }

    /**
     * @depends testIdConstructor
     *
     * @param JobEntity $job
     *
     * @throws \ReflectionException
     */
    public function testAddTaskWillCallTaskSetJob(JobEntity $job)
    {
        $task = $this->getMockForAbstractClass(TaskEntityInterface::class);

        $task
            ->expects($spy = $this->any())
            ->method('setJob');

        $job->addTask($task);

        $this->assertCount(1, $spy->getInvocations());
    }
}
