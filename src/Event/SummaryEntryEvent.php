<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 03/02/19
 * Time: 20.57
 */

namespace Bnza\JobManagerBundle\Event;


use Bnza\JobManagerBundle\Info\InfoInterface;
use Bnza\JobManagerBundle\Summary\Entry\AbstractInfoEntry;
use Symfony\Contracts\EventDispatcher\Event;

class SummaryEntryEvent extends Event
{
    const NAME = 'bnza.job_manager.info.summary_entry';
    /**
     * @var InfoInterface
     */
    protected $entry;

    public function __construct(AbstractInfoEntry $entry)
    {
        $this->entry = $entry;
    }

    public function getEntry(): AbstractInfoEntry
    {
        return $this->entry;
    }

    public function getRunnable(): InfoInterface
    {
        return $this->getEntry()->getInfo();
    }

}
