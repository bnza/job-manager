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
        $defaultMessage = 'Job cancelled by user input';
        if ($message) {
            $defaultMessage .= ": $message";
        }
        parent::__construct($defaultMessage, $code, $previous);
    }
}
