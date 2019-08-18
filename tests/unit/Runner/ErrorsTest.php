<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner;

use Bnza\JobManagerBundle\Exception\JobManagerException;
use Bnza\JobManagerBundle\Runner\Errors;

class ErrorsTest extends \PHPUnit\Framework\TestCase
{
    public function errorsProvider()
    {
        return [
            [
                'some_key',
                [1, 2, 3],
                null
            ],
            [
                'a_key',
                ['the' => 'value'],
                \LogicException::class,
            ]
        ];
    }

    /**
     * @dataProvider errorsProvider
     * @param string $key
     * @param array $value
     * @param string|void $class
     * @param string $expected
     */
    public function testToString(string $key, array $value, $class)
    {
        $errors = new Errors();
        if ($class) {
            $class = new $class;
        }
        $errors->push($key, $value, $class);
        $string = (string) $errors;
        $errors2 = json_decode($string, true);
        $error = $errors2[0];
        $this->assertEquals($error['key'], $key);
        $this->assertEquals($error['value'], $value);
        if ($class) {
            $this->assertEquals($error['exception'], (string) $class);
        }
    }
}
