<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runnable;

use Bnza\JobManagerBundle\Exception\JobManagerException;
use Bnza\JobManagerBundle\Runnable\Status;

class StatusTest extends \PHPUnit\Framework\TestCase
{
    public function testEmptyConstructor()
    {
        $status = new Status();
        $this->assertEquals(0b0000, $status->get());

        return $status;
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param Status $status
     *
     * @return Status
     */
    public function testToString(Status $status)
    {
        $this->assertEquals('0', (string) $status);

        return $status;
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerException
     * @expectedExceptionMessage Cannot end a not running job
     * @depends testEmptyConstructor
     */
    public function testEndNotRunningThrowsException(Status $status)
    {
        $status = new Status();
        $status->end();
    }

    /**
     * @depends testEmptyConstructor
     *
     * @param Status $status
     *
     * @return Status
     *
     * @throws JobManagerException
     */
    public function testRun(Status $status)
    {
        $status->run();
        $this->assertEquals(Status::RUNNING, $status->get());

        return $status;
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerException
     * @expectedExceptionMessageRegExp /Only clean statuses can be ran \[.+\]/
     * @depends testRun
     *
     * @param Status $status
     */
    public function testReRunThrowsException(Status $status)
    {
        $status->run();
    }

    /**
     * @depends testRun
     *
     * @param Status $status
     */
    public function testIsRunning(Status $status)
    {
        $this->assertTrue($status->isRunning());
    }

    /**
     * @depends testRun
     *
     * @param Status $status
     *
     * @return Status
     *
     * @throws JobManagerException
     */
    public function testEnd(Status $status)
    {
        $status->end();
        $this->assertEquals(0b0000, $status->get());

        return $status;
    }

    /**
     * @depends testEnd
     *
     * @param Status $status
     */
    public function testIsNotRunning(Status $status)
    {
        $this->assertFalse($status->isRunning());
    }

    /**
     * @depends testRun
     *
     * @param Status $status
     *
     * @return Status
     */
    public function testError(Status $status)
    {
        $status->error();
        $this->assertTrue((bool) ($status->get() & Status::ERROR));

        return $status;
    }

    /**
     * @depends testError
     *
     * @param Status $status
     */
    public function testIsError(Status $status)
    {
        $this->assertTrue($status->isError());
    }

    /**
     * @depends testError
     *
     * @param Status $status
     */
    public function testIsNotRunningAfterError(Status $status)
    {
        $this->assertFalse($status->isRunning());
    }

    /**
     * @depends testRun
     *
     * @param Status $status
     *
     * @return Status
     */
    public function testSuccess(Status $status)
    {
        $status->success();
        $this->assertTrue((bool) ($status->get() & Status::SUCCESS));

        return $status;
    }

    /**
     * @depends testSuccess
     *
     * @param Status $status
     */
    public function testIsSuccessful(Status $status)
    {
        $this->assertTrue($status->isSuccessful());
    }

    /**
     * @depends testSuccess
     *
     * @param Status $status
     */
    public function testIsNotRunningAfterSuccess(Status $status)
    {
        $this->assertFalse($status->isRunning());
    }
}
