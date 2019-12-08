<?php

namespace Bnza\JobManagerBundle\Tests\Unit\Status;

use PHPUnit\Framework\TestCase;
use Bnza\JobManagerBundle\Status\StatusInfo;

class StatusInfoTest extends TestCase
{
    public function getterProvider()
    {
        return [
            [0b0000000, 'isClean', true],
            [0b1111111, 'isClean', false],
            [0b0000001, 'isAttached', true],
            [0b1111110, 'isAttached', false],
            [0b0000010, 'isReady', true],
            [0b1111101, 'isReady', false],
            [0b0000100, 'isRunning', true],
            [0b1111011, 'isRunning', false],
            [0b0001000, 'isSkipped', true],
            [0b1110111, 'isSkipped', false],
            [0b0010000, 'isSuccess', true],
            [0b1101111, 'isSuccess', false],
            [0b0100000, 'isError', true],
            [0b1011111, 'isError', false],
            [0b1000000, 'isCancelled', true],
            [0b0111111, 'isCancelled', false],
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
