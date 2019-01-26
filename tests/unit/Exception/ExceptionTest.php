<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */


namespace Bnza\JobManagerBundle\Tests\Exception;

use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;
use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
use Bnza\JobManagerBundle\Exception\JobManagerException;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     * @expectedExceptionMessage [6dcd4ce23d88e2ee9568ba546c007c63d9131c1b] job entity not found
     */
    public function testJobManagerEntityNotFoundExceptionWithStringJobId()
    {
        throw new JobManagerEntityNotFoundException(sha1('A'));
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     * @expectedExceptionMessage [6dcd4ce23d88e2ee9568ba546c007c63d9131c1b] job entity not found
     */
    public function testJobManagerEntityNotFoundExceptionWithArrayJobId()
    {
        throw new JobManagerEntityNotFoundException([sha1('A')]);
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     * @expectedExceptionMessage [6dcd4ce23d88e2ee9568ba546c007c63d9131c1b.9] task entity not found
     */
    public function testJobManagerEntityNotFoundExceptionWithArrayTaskId()
    {
        throw new JobManagerEntityNotFoundException([sha1('A'), 9]);
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException
     * @expectedExceptionMessage Job cancelled by user input
     */
    public function testJobManagerCancelledJobExceptionWithEmptyConstructor()
    {
        throw new JobManagerCancelledJobException();
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException
     * @expectedExceptionMessage Job cancelled by user input: Dummy message
     */
    public function testJobManagerCancelledJobExceptionWithMessage()
    {
        throw new JobManagerCancelledJobException('Dummy message');
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerException
     * @expectedExceptionMessage
     */
    public function testJobManagerExceptionWithEmptyConstructor()
    {
        throw new JobManagerException();
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerException
     * @expectedExceptionMessage Dummy message
     */
    public function testJobManagerExceptionWithMessage()
    {
        throw new JobManagerException('Dummy message');
    }
}
