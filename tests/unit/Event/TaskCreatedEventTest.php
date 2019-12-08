<?php

namespace Bnza\JobManagerBundle\Tests\Unit\Event;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Event\AbstractTaskEvent;
use PHPUnit\Framework\TestCase;

class AbstractTaskEventTest extends TestCase
{
    public function testEventClass()
    {
        $entity = $this->getMockForAbstractClass(TaskEntityInterface::class);
        $event = $this
            ->getMockBuilder(AbstractTaskEvent::class)
            ->setConstructorArgs([$entity])
            ->enableOriginalConstructor()
            ->setMethods(null)
            ->getMock();
        $this->assertEquals($entity, $event->getTaskEntity());
    }
}
