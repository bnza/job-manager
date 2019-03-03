<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Command;


use Bnza\JobManagerBundle\Runner\Job\JobInterface;
use Bnza\JobManagerBundle\Info\TaskInfoInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Info\JobInfoInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractJobCommand extends Command
{
    /**
     * @var JobInterface
     */
    protected $job;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ConsoleSectionOutput[]
     */
    protected $sections = [];

    /**
     * @var ProgressBar[]
     */
    protected $progressBars = [
        'tasks' => []
    ];

    /**
     * @var ConsoleSectionOutput
     */
    protected $statusSection;

    /**
     * @var ObjectManagerInterface
     */
    protected $om;

    /**
     * @return JobInterface
     */
    protected function getJob(): JobInterface
    {
        return $this->job;
    }

    /**
     * @param string $key
     * @param int|null $num
     * @return bool|ConsoleSectionOutput
     */
    protected function getSection(string $key, int $num = 0)
    {
        if(\array_key_exists($key, $this->sections)) {
            $section = $this->sections[$key];
            if ($key === 'tasks') {
                if (!isset($section[$num])) {
                    $taskSection = $this->output->section();
                    $this->sections[$key][$num] =  $taskSection;
                }
                $section = $this->sections[$key][$num];
            }
            return $section;
        } else {
            return false;
        }
    }

    protected function getTaskProgressBar(TaskInfoInterface $task): ProgressBar
    {
        $num = $task->getNum();
        if (!isset($this->progressBars['tasks'][$num])) {
            //$this->progressBars['tasks'][$num] = $pb = new ProgressBar($this->getSection('tasks', $num), $task->getStepsNum());
            $this->progressBars['tasks'][$num] = $pb = new ProgressBar($this->output->section(), $task->getStepsNum());
            $pb->setFormatDefinition('task', '  [%num:03d%]  %current%/%max% [%bar%]: %message%');
            $pb->setFormat('task');
        }
        return $this->progressBars['tasks'][$num];
    }

    public function __construct(ObjectManagerInterface $om)
    {
        $this->om = $om;
        parent::__construct();
    }

    protected function getObjectManager(): ObjectManagerInterface
    {
        return $this->om;
    }

    protected function setUpOutputSections(OutputInterface $output)
    {
        $this->output = $output;
        if ($output instanceof ConsoleOutput) {
            $this->sections['header'] = $output->section();
            $this->sections['status'] = $output->section();
            //$this->sections['overall'] = $output->section();
            $this->sections['tasks'] = [];
        }
    }

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->setUpOutputSections($output);
    }

    public function displayJobHeader(JobInfoInterface $info)
    {
        $section = $this->getSection('header');
        $section->writeln('Runner: ' . $info->getId());
        $section->writeln(sprintf('%s [%s]', $info->getDescription(), $info->getName()));
    }

    public function updateOverallProgress(JobInfoInterface $info, bool $complete = false)
    {
        if (!\array_key_exists('overall', $this->progressBars)) {
            //$pb = $this->progressBars['overall'] = new ProgressBar($this->getSection('overall'), $info->getStepsNum());
            $pb = $this->progressBars['overall'] = new ProgressBar($this->output->section(), $info->getStepsNum());
            $pb->setFormatDefinition('overall', ' %current%/%max%: %message%');
            $pb->setFormat('overall');
        } else {
            $pb = $this->progressBars['overall'];
        }
        try {
            $message = $info->getCurrentTask()->getDescription();
        } catch (\Throwable $t) {
            $message = '';
        }
        $pb->setMessage($message);

        if ($complete) {
            $pb->finish();
        } else {
            $pb->setProgress($info->getCurrentStepNum() + 1);
        }

    }

    public function updateStatusDisplay(JobInfoInterface $info)
    {
        if ($section = $this->getSection('status')) {
            if ($info->isRunning()) {
                $message = '<options=bold,blink>Running...</>';
            } elseif ($info->isSuccessful()) {
                $message = '<info>Success</info>';
            } elseif ($info->isError()) {
                $message = '<error>Error</error>';
            } else {
                throw new \LogicException('Invalid job status');
            }
            $section->overwrite($message);
        }
    }

    public function setTaskComplete(TaskInfoInterface $info)
    {
        $pb = $this->getTaskProgressBar($info);
        $pb->setMessage('<info>✓</info> '.$info->getMessage());
        $pb->finish();
        $this->updateOverallProgress($info->getJob());
    }

    public function setJobComplete(JobInfoInterface $info)
    {
        $this->updateOverallProgress($info, true);
        $this->updateStatusDisplay($info);
    }

    public function updateTaskProgress(TaskInfoInterface $info)
    {
        $pb = $this->getTaskProgressBar($info);
        $pb->setMessage($info->getMessage());
        $pb->setMessage($info->getNum(), 'num');
        $pb->setProgress($info->getCurrentStepNum());
    }

    public function updateDisplay(JobInfoInterface $info)
    {
        $this->updateStatusDisplay($info);
        $this->updateOverallProgress($info);
    }
}
