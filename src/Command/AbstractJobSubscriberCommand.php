<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Command;

use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\TaskStartedEvent;
use Bnza\JobManagerBundle\Event\TaskEndedEvent;
use Bnza\JobManagerBundle\Event\TaskStepStartedEvent;
use Bnza\JobManagerBundle\Event\TaskStepEndedEvent;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class AbstractJobSubscriberCommand extends AbstractJobCommand implements EventSubscriberInterface
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    abstract protected function setUpJob(): JobInterface;

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            JobStartedEvent::NAME => 'onJobStarted',
            JobEndedEvent::NAME => 'onJobEnded',
            TaskStartedEvent::NAME => 'onTaskStarted',
            TaskEndedEvent::NAME => 'onTaskEnded',
            TaskStepStartedEvent::NAME => 'onTaskStepStarted',
            TaskStepEndedEvent::NAME => 'onTaskStepEnded',
        ];
    }

    public function __construct(ObjectManagerInterface $om, EventDispatcherInterface $dispatcher)
    {
        parent::__construct($om);
        $this->dispatcher = $dispatcher;
    }

    protected function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }

    public function onJobStarted(JobStartedEvent $event)
    {
        $job = $event->getJob();
        $this->displayJobHeader($job);
        $this->updateStatusDisplay($job);
        $this->updateOverallProgress($job);
    }

    public function onJobEnded(JobEndedEvent $event)
    {
        $this->setJobComplete($event->getJob());
    }

    public function onTaskStarted(TaskStartedEvent $event)
    {
        $this->updateTaskProgress($event->getTask());
    }

    public function onTaskEnded(TaskEndedEvent $event)
    {
        $this->setTaskComplete($event->getTask());
    }

    public function onTaskStepStarted(TaskStepStartedEvent $event)
    {
        //$this->updateTaskProgress($event->getTask());
    }

    public function onTaskStepEnded(TaskStepEndedEvent $event)
    {
        $this->updateTaskProgress($event->getTask());
    }
}
