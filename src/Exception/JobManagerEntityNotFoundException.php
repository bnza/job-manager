<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Exception;

use Throwable;

class JobManagerEntityNotFoundException extends JobManagerException
{
    public function __construct($ids, int $code = 0, Throwable $previous = null)
    {
        if (is_array($ids)) {
            if (1 == count($ids)) {
                $type = 'job';
                $id = $ids[0];
            } else {
                $type = 'task';
                $id = implode('.', $ids);
            }
        }
        parent::__construct("[$id] $type entity not found", $code, $previous);
    }
}
