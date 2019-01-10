<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Command;

use Bnza\JobManagerBundle\Job\AbstractRunnableInfo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;

abstract class AbstractJobCommand extends Command
{
    /**
     * @var ConsoleSectionOutput
     */
    protected $statusSection;

    public function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->statusSection = $output->section();
    }

    protected function writeStatus(AbstractRunnableInfo $info)
    {
        if ($info->isRunning()) {
            $message = '<options=bold,blink>Running...</>';
        } elseif ($info->isSuccessful()) {
            $message = '<info>Success</info>';
        } elseif ($info->isError()) {
            $message = '<error>Error</error>';
        }
        $this->statusSection->overwrite($message);
    }
}
