<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests;

use Bnza\JobManagerBundle\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function provideStatuses(): array
    {
        return [
            [0, Status::RUNNING, false],
            [0, Status::SKIPPED, false],
            [Status::RUNNING, Status::RUNNING, true],
            [Status::RUNNING, Status::SKIPPED, false],
        ];
    }

    public function provideStatusSetter(): array
    {
        return [
            'running' => ['Running'],
            'skipped' => ['Skipped'],
            'success' => ['Success'],
            'error' => ['Error'],
            'cancelled' => ['Cancelled'],
        ];
    }

    public function testEmptyConstructor()
    {
        $status = new Status();
        $this->assertEquals((string) 0b0000, (string) $status);
        return $status;
    }

    /**
     * @dataProvider provideStatuses
     * @param int $value
     * @param int $condition
     * @param bool $expected
     */
    public function testMethodIsReturnExpectedValue(int $value, int $condition, bool $expected)
    {
        $status = new Status($value);
        $this->assertEquals($expected, $status->is($condition));
    }

    /**
     * @dataProvider provideStatusSetter
     * @param string $prop
     */
    public function testGetterSetterMethods(string $prop)
    {
        $status = new Status();
        $status->{"set$prop"}();
        $this->assertTrue($status->{"is$prop"}());
    }


}
