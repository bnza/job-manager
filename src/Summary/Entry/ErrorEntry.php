<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Summary\Entry;

use Psr\Log\LogLevel;

class ErrorEntry extends AbstractInfoEntry
{
    public function getLevel(): string
    {
        return LogLevel::ERROR;
    }
}
