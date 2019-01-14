<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Command;


use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Job\JobInfoInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Helper\ProgressBar;

abstract class AbstractJobCommand extends Command
{
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
    protected $progressBars = [];

    /**
     * @var ConsoleSectionOutput
     */
    protected $statusSection;

    /**
     * @var ObjectManagerInterface
     */
    protected $om;

    /**
     * @param string $key
     * @return bool|ConsoleSectionOutput
     */
    protected function getSection(string $key)
    {
        if(\array_key_exists($key, $this->sections)) {
            return $this->sections[$key];
        } else {
            return false;
        }
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

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof ConsoleOutput) {
            $this->sections['status'] = $output->section();
            $this->sections['overall'] = $output->section();
        } else {
            $this->output = $output;
        }

    }

    public function displayJobHeader(OutputInterface $output, JobInfoInterface $info)
    {
        $output->writeln('Job: ' . $info->getId());
        $output->writeln('Name: ' . $info->getName());
    }

    public function updateOverallProgress(JobInfoInterface $info)
    {
        if (!\array_key_exists('overall', $this->progressBars)) {
            $pb = $this->progressBars['overall'] = new ProgressBar($this->getSection('overall'), $info->getStepsNum());
            $pb->setFormatDefinition('overall', ' %current%/%max%: %message%');
            $pb->setFormat('overall');
        } else {
            $pb = $this->progressBars['overall'];
        }
        $pb->setMessage($info->getCurrentTask()->getName());
        $pb->setProgress($info->getCurrentStepNum());
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

    public function updateDisplay(JobInfoInterface $info)
    {
        $this->updateStatusDisplay($info);
        $this->updateOverallProgress($info);
    }
}
