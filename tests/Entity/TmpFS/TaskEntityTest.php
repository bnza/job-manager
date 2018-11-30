<?php
/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 26/11/18
 * Time: 16.58.
 */

namespace Bnza\JobManagerBundle\Tests\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;

class TaskEntityTest extends \PHPUnit\Framework\TestCase
{
    private $jobId = '6dcd4ce23d88e2ee9568ba546c007c63d9131c1b';
    private $taskNum = 1;

    public function testIdConstructor()
    {
        $entity = new TaskEntity($this->jobId, $this->taskNum);
        $this->assertInstanceOf(JobEntity::class, $entity->getJob());

        return $entity;
    }

    public function testJobConstructor()
    {
        $job = $this->createMock(JobEntity::class);
        $entity = new TaskEntity($job, $this->taskNum);
        $this->assertSame($job, $entity->getJob());
    }

    public function testNoJobConstructor()
    {
        $entity = new TaskEntity(false, $this->taskNum);
        $this->assertNull($entity->getJob());
        return $entity;
    }

    public function wrongJobParameterProvider()
    {
        return [
          [1],
          [true],
          [['wrong']]
        ];
    }

    /**
     * @dataProvider wrongJobParameterProvider
     * @expectedException           \InvalidArgumentException
     * @expectedExceptionMessage    Invalid Job parameter: only job ID string or JobEntity instance are permitted
     */
    public function testWrongJobParameterConstructor($job)
    {
        $entity = new TaskEntity($job, $this->taskNum);
    }

    public function propertyProvider()
    {
        return [
            ['Num', $this->taskNum],
            ['CurrentStepNum', 2],
            ['StepsNum', 3],
        ];
    }

    /**
     * @depends      testIdConstructor
     * @dataProvider propertyProvider
     *
     * @param string $prop
     * @param $value
     * @param TaskEntity $task
     */
    public function testSetGetClass(string $prop, $value, TaskEntity $task)
    {
        $task->{"set$prop"}($value);
        $this->assertEquals($value, $task->{"get$prop"}());
    }


    /**
     * @depends testNoJobConstructor
     */
    public function testSetJob(TaskEntity $task)
    {
        $job = $this->createMock(JobEntity::class);
        $task->setJob($job);
        $this->assertSame($job, $task->getJob());
    }
}
