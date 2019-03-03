<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Summary;

use Bnza\JobManagerBundle\Event\JobEndedEvent;
use Bnza\JobManagerBundle\Event\JobStartedEvent;
use Bnza\JobManagerBundle\Event\SummaryEntryEvent;
use Bnza\JobManagerBundle\Event\TaskStartedEvent;
use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\Runner\Task\TaskInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Summary implements EventSubscriberInterface
{
    /**
     * @var
     */
    private $baseWorkDir;

    /**
     * @var array
     */
    private $entries = [];

    public function __construct(string $baseWorkDir)
    {
        $this->baseWorkDir = $baseWorkDir;
    }

    private function getBaseWorkDir()
    {
        return $this->baseWorkDir;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JobStartedEvent::NAME => 'onJobStarted',
            JobEndedEvent::NAME => 'onJobEnded',
            TaskStartedEvent::NAME => 'onTaskStarted',
            SummaryEntryEvent::NAME => 'addLog'
        ];
    }

    public function getLogEntries(string $id = '', int $num = null)
    {

        if (\is_null($num)) {
            $entries = $this->getJobEntry($id);
        } else {
            $entries = $this->getTaskEntry($num, $id);
        }

        if ($entries && \array_key_exists('log', $entries)) {
            return $entries['log'];
        } else {
            return [];
        }
    }

    public function getTaskEntry(int $num, string $id = ''): array
    {
        $entries = $this->getJobEntry($id);
        if ($entries && \array_key_exists('tasks', $entries)) {
            return \array_key_exists($num, $entries['tasks']) ? $entries['tasks'][$num] : [];
        } else {
            return [];
        }
    }

    /**
     * Return the job's entry with the given id or the first entry
     *
     * @param string $id
     * @return array
     */
    public function getJobEntry(string $id = ''): array
    {
        $entries = $this->getEntries();
        if ($id) {
            return \array_key_exists($id, $entries) ? $entries[$id] : [];
        } else {
            return count($entries) ? \reset($entries) : [];
        }
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Add an entry to $entries with job's sha1 key to store job related data
     * @param JobStartedEvent $event
     */
    public function onJobStarted(JobStartedEvent $event)
    {
     $this->addJob($event->getJob());
    }

    /**
     * Persist entries to a json file in job's work directory
     * @param JobEndedEvent $event
     */
    public function onJobEnded(JobEndedEvent $event)
    {
        $id = $event->getJob()->getId();
        $workDir = $this->getBaseWorkDir().DIRECTORY_SEPARATOR.$id;
        if (!file_exists($workDir)) {
            mkdir($workDir, 0700, true);
        }
        $filename = $workDir.DIRECTORY_SEPARATOR.'summary.json';
        \file_put_contents($filename, \json_encode($this->getEntries()));

    }

    /**
     * Add task related data in $entries[$job_id][$task_num]
     * @param TaskStartedEvent $event
     */
    public function onTaskStarted(TaskStartedEvent $event)
    {
        $this->addTask($event->getTask());
    }

    public function addLog(SummaryEntryEvent $event)
    {
        $runner = $event->getRunnable();
        if ($runner instanceof JobInterface) {
            $task = null;
            $job = $runner;
        } else if  ($runner instanceof TaskInterface) {
            $task = $runner;
            $job = $task->getJob();
        }

        if (!$this->getJobEntry($job->getId())) {
            $this->addJob($job);
        }

        if ($task) {
            if (!$this->getTaskEntry($task->getNum(), $job->getId())) {
                $this->addTask($task);
            }
            $this->entries[$job->getId()]['tasks'][$task->getNum()]['log'][] = $event->getEntry()->asArray();
        } else {
            $this->entries[$job->getId()]['log'][] = $event->getEntry()->asArray();
        }
    }

    protected function addJob(JobInterface $job)
    {
        $entry = $job->asArray();
        $entry['tasks'] = [];
        $entry['log'] = [];
        $this->entries[$job->getId()] = $entry;
    }

    protected function addTask(TaskInterface $task)
    {
        $id = $task->getJob()->getId();
        if (!$this->getJobEntry($id)) {
            $this->addJob($task->getJob());
        }
        $entry = $task->asArray();
        $entry['log'] = [];
        $this->entries[$id]['tasks'][$task->getNum()] = $entry;
    }
}
