<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('jobId', InputArgument::REQUIRED, 'The job ID');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
