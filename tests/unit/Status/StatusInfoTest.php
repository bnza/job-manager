<?php

namespace Bnza\JobManagerBundle\Tests\Status;

use PHPUnit\Framework\TestCase;
use Bnza\JobManagerBundle\Status\StatusInfo;

class StatusInfoTest extends TestCase
{
    public function getterProvider()
    {
        return [
            [0b00000, 'isNew', true],
            [0b11111, 'isNew', false],
            [0b00001, 'isRunning', true],
            [0b11110, 'isRunning', false],
            [0b00010, 'isSkipped', true],
            [0b11101, 'isSkipped', false],
            [0b00100, 'isSuccess', true],
            [0b11011, 'isSuccess', false],
            [0b01000, 'isError', true],
            [0b10111, 'isError', false],
            [0b10000, 'isCancelled', true],
            [0b01111, 'isCancelled', false],
        ];
    }

    public function testEmptyConstructor()
    {
        $this->assertEquals('0', new StatusInfo());
    }

    public function testConstructor()
    {
        $status = new StatusInfo(44);
        $this->assertEquals('44', (string) $status);
    }

    /**
     * @dataProvider getterProvider
     */
    public function testGetters(int $status, string $method, bool $expected)
    {
        $info = new StatusInfo($status);
        $this->assertEquals($expected, $info->$method());
    }
}
