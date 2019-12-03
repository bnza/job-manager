<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Unit\Status;

use Bnza\JobManagerBundle\Status\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
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
