<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Command;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;
use Bnza\JobManagerBundle\Job\JobInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class JobInfoCommand extends AbstractJobCommand
{
    protected static $defaultName = 'bnza:job-manager:info';

    protected function configure()
    {
        $this
            ->setDescription('Retrieves and display job\'s information')
            ->setHelp('This command retrieves and display the information about the job identified by given ID')
            ->addArgument('job_id', InputArgument::REQUIRED, 'The job ID')
            ->addOption(
                'interval',
                'i',
                InputOption::VALUE_REQUIRED,
                'The interval between refresh cycles in milliseconds. Default 250'
            )
            ->addOption(
                'follow', 'f',
                InputOption::VALUE_NONE,
                'Output is refreshed while job is running'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $om = $this->getObjectManager();
        $id = $input->getArgument('job_id');

        try {
            $info = new JobInfo($om, $om->find('job', $id));
            $this->displayJobHeader($output, $info);
            $this->updateDisplay($info);
        } catch (JobManagerEntityNotFoundException $e) {
            $output->writeln("No job with ID <comment>$id</comment> found");
            return 1;
        }

        if ($input->getOption('follow')) {
            $interval = ($input->getOption('follow') || 250) * 1000;
            while ($info->isRunning()) {
                sleep($interval);
                $this->updateDisplay($info);
            }
        }

    }
}
