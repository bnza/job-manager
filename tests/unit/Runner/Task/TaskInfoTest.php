<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Task;

use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Info\TaskInfo;
use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;

class TaskInfoTest extends \PHPUnit\Framework\TestCase
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
        $num = (int) rand(0, 100);
        $entity = new TaskEntity(sha1(microtime()), $num);

        $om = $this->getObjectManagerMock($entity);

        $info = new TaskInfo($om, get_class($entity), $entity->getId());

        $this->assertEquals($num, $info->getNum());

        return $info;
    }
}
