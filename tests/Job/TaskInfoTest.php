<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Job;

use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Job\TaskInfo;
use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;

class TaskInfoTest extends \PHPUnit\Framework\TestCase
{
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
