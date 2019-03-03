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
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractJobListenerCommand extends AbstractJobCommand
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    abstract protected function setUpJob(): JobInterface;

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setUpOutputSections($output);
        $this->job = $this->setUpJob();
        $this->addListeners();
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

    protected function addListeners()
    {
        $dispatcher = $this->getDispatcher();
        $dispatcher->addListener(JobStartedEvent::NAME, [$this, 'onJobStarted']);
        $dispatcher->addListener(JobEndedEvent::NAME, [$this, 'onJobEnded']);
        $dispatcher->addListener(TaskStartedEvent::NAME, [$this, 'onTaskStarted']);
        $dispatcher->addListener(TaskEndedEvent::NAME, [$this, 'onTaskEnded']);
        $dispatcher->addListener(TaskStepStartedEvent::NAME, [$this, 'onTaskStepStarted']);
        $dispatcher->addListener(TaskStepEndedEvent::NAME, [$this, 'onTaskStepEnded']);
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
