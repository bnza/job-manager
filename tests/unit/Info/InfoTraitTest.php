<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Info;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Info\InfoTrait;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Runner\Status;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;
use Bnza\JobManagerBundle\Tests\UtilsTrait;

class InfoTraitTest extends \PHPUnit\Framework\TestCase
{
    use UtilsTrait;
    use MockUtilsTrait;

    /**
     * @var int
     */
    private $jobId;

    /**
     * @var string
     */
    private $taskNum;

    public function setUp()
    {
        $this->jobId = sha1(microtime());
        $this->taskNum = (int)mt_rand(0, 100);
    }

    public function testMethodAsArray()
    {
        $sha1 = sha1(microtime());
        $mockStatus = $this->getMockStatus(Status::class, ['get']);
        $mockStatus->expects($this->once())->method('get')->willReturn(1);

        $mockEntity = $this->getMockForTypeWithMethods(RunnableEntityInterface::class, []);
        $mockEntity->expects($this->once())->method('getStatus')->willReturn($mockStatus);

        $mock = $this->getMockForTypeWithMethods(
            InfoTrait::class,
            ['getName', 'getClass', 'getDescription', 'getStepsNum', 'getEntity', 'getId']
        );

        $mock->method('getId')->willReturn($sha1);

        $expected = [
            'name' => 'foo',
            'class' => \get_class($mock),
            'description' => 'bar',
            'steps_num' => 3,
            'status' => 1,
            'id' => $sha1
        ];

        $mock->expects($this->once())->method('getName')->willReturn($expected['name']);
        $mock->expects($this->once())->method('getClass')->willReturn($expected['class']);
        $mock->expects($this->once())->method('getDescription')->willReturn($expected['description']);
        $mock->expects($this->once())->method('getStepsNum')->willReturn($expected['steps_num']);
        $mock->method('getEntity')->willReturn($mockEntity);


        $this->assertEquals($expected, $mock->asArray());
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
        $info = $this->getRunnableInfoTraitMock($entity);
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
        $entity = new TaskEntity(sha1(microtime()), (int)mt_rand(0, 100));
        $this->handleEntityProp($entity, 'set', $prop, $value);
        $info = $this->getRunnableInfoTraitMock($entity);
        $method = $this->getPropertyInflectedMethod('get', $prop);
        $this->assertEquals($entity->$method(), $info->$method());
    }

    public function testRefreshJob()
    {
        $entity = new JobEntity();
        $this->assertRefresh($entity);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $prop
     */
    public function testRefreshJobProperty(string $prop)
    {
        $entity = new JobEntity();
        $this->assertRefresh($entity, $prop);
    }

    public function testRefreshTask()
    {
        $entity = new TaskEntity(sha1(microtime()), (int)mt_rand(0, 100));
        $this->assertRefresh($entity);
    }

    /**
     * @dataProvider propertiesProvider
     *
     * @param string $prop
     */
    public function testRefreshTaskProperty(string $prop)
    {
        $entity = new TaskEntity(sha1(microtime()), (int)mt_rand(0, 100));
        $this->assertRefresh($entity, $prop);
    }

    public function propertiesProvider()
    {
        return [
            ['class', self::class],
            ['name', 'Task name'],
            ['steps_num', 4],
            ['current_step_num', 3],
            ['description', 'Dummy description'],
            ['message', 'Dummy message']
        ];
    }

    protected function assertRefresh(RunnableEntityInterface $entity, string $prop = '')
    {
        $om = $this->getObjectManagerMock($entity);
        $om->expects($spy = $this->once())
            ->method('refresh');
        $info = $this->getRunnableInfoTraitMock($entity, $om);
        $info->refresh($prop);
        $invocations = $spy->getInvocations();
        $this->assertCount(1, $invocations);
        $this->assertEquals($prop, $invocations[0]->getParameters()[1]);
    }

    protected function getRunnableInfoTraitMock(RunnableEntityInterface $entity, ObjectManager $om = null)
    {
        if (!$om) {
            $om = $this->getObjectManagerMock($entity);
        }

        $info = $this->getMockForTrait(
            InfoTrait::class,
            [],
            '',
            false,
            false,
            true,
            ['getObjectManager', 'getEntity']
        );

        $info->method('getEntity')->willReturn($entity);
        $info->method('getObjectManager')->willReturn($om);

        return $info;
    }

    protected function getObjectManagerMock(RunnableEntityInterface $entity)
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
}
