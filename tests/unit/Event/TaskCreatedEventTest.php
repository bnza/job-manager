<?php

namespace Bnza\JobManagerBundle\Tests\Unit\Event;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Event\TaskCreatedEvent;
use PHPUnit\Framework\TestCase;

class TaskCreatedEventTest extends TestCase
{
    public function testEventClass()
    {
        $entity = $this->getMockForAbstractClass(TaskEntityInterface::class);
        $event = new TaskCreatedEvent($entity);
        $this->assertEquals($entity, $event->getTaskEntity());
    }
}
