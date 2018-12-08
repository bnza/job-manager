<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Job\AbstractInfoGetter;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Tests\EntityPropertyHandlerTrait;

class AbstractInfoGetterTest extends \PHPUnit\Framework\TestCase
{
    use EntityPropertyHandlerTrait;

    private $jobId = 'ae4f281df5a5d0ff3cad6371f76d5c29b6d953ec';
    private $taskNum = 83;

    private function getObjectManagerMock(JobManagerEntityInterface $entity)
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

    private function getAbstractInfoMock(JobManagerEntityInterface $entity, ObjectManager $om = null)
    {
        if (!$om) {
            $om = $this->getObjectManagerMock($entity);
        }

        if ($entity instanceof JobEntity) {
            $jobId = $entity->getId();
            $taskNum = -1;
        } elseif ($entity instanceof TaskEntity) {
            $jobId = $entity->getJob()->getId();
            $taskNum = $entity->getNum();
        }

        $info = $this->getMockForAbstractClass(
            AbstractInfoGetter::class,
            [
                $om,
                get_class($entity),
                $jobId,
                $taskNum,
            ]
        );

        return $info;
    }

    public function propertiesProvider()
    {
        return [
            ['class', self::class],
            ['name', 'Task name'],
            ['steps_num', 4],
            ['current_step_num', 3],
        ];
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $prop
     * @param $value
     */
    public function testGetJobProperty(string $prop, $value)
    {
        $entity = new JobEntity();
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $info = $this->getAbstractInfoMock($entity);
        $method = $this->getPropertyInflectedMethod('get', $prop);
        $this->assertEquals($entity->$method(), $info->$method());
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $prop
     * @param $value
     */
    public function testGetTaskProperty(string $prop, $value)
    {
        $entity = new TaskEntity(sha1(microtime()), (int) mt_rand(0, 100));
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $info = $this->getAbstractInfoMock($entity);
        $method = $this->getPropertyInflectedMethod('get', $prop);
        $this->assertEquals($entity->$method(), $info->$method());
    }

    private function _testRefresh(JobManagerEntityInterface $entity, string $prop = '')
    {
        $om = $this->getObjectManagerMock($entity);
        $om->expects($spy = $this->once())
            ->method('refresh');
        $info = $this->getAbstractInfoMock($entity, $om);
        if ('' == $prop) {
            $info->refresh();
        } else {
            $info->refresh($prop);
        }
        $invocations = $spy->getInvocations();
        $this->assertCount(1, $invocations);
        $this->assertEquals($prop, $invocations[0]->getParameters()[1]);
    }

    public function testRefreshJob()
    {
        $entity = new JobEntity();
        $this->_testRefresh($entity);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $prop
     */
    public function testRefreshJobProperty(string $prop)
    {
        $entity = new JobEntity();
        $this->_testRefresh($entity, $prop);
    }

    public function testRefreshTask()
    {
        $entity = new TaskEntity(sha1(microtime()), (int) mt_rand(0, 100));
        $this->_testRefresh($entity);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $prop
     * @param $value
     */
    public function testRefreshTaskProperty(string $prop)
    {
        $entity = new TaskEntity(sha1(microtime()), (int) mt_rand(0, 100));
        $this->_testRefresh($entity, $prop);
    }
}
