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

class JobManagerCancelledJobException extends JobManagerException
{
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        $message = $message ?: 'Job cancelled by user input';
        parent::__construct($message, $code, $previous);
    }
}
