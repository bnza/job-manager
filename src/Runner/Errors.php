<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner;


class Errors
{
    private $errors = [];

    public function push(string $key, array $value, \Throwable $t = null)
    {
        array_push($this->errors, [
            'key' => $key,
            'value' => $value,
            'exception' => (string) $t
        ]);
    }

    public function __toString()
    {
        return \json_encode($this->errors);
    }
}
