<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Job;

use Bnza\JobManagerBundle\Runner\Job\WorkDirTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\ParameterBag;


class WorkDirTraitTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var string
     */
    private $baseWorkDir;

    /**
     * @var string
     */
    private $jobWorkDir;

    /**
     * @var string
     */
    private $jobId;

    private function getMockTrait()
    {
        $mock = $this->getMockForTrait(
            WorkDirTrait::class,
            [],
            "",
            false,
            false,
            true,
            ['getParameters']
        );

        $mock
            ->method('getId')
            ->willReturn($this->jobId);

        $mock
            ->method('getParameters')
            ->willReturn(new ParameterBag());

        return $mock;
    }

    public function setUp()
    {
        $baseWorkDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'test-job-base-work-dir';
        if (\file_exists($baseWorkDir)) {
            $fs = new Filesystem();
            $fs->remove($baseWorkDir);
        }
        \mkdir($baseWorkDir);
        $this->baseWorkDir = $baseWorkDir;
        $this->jobId = sha1(microtime());
        $this->jobWorkDir = $this->baseWorkDir.DIRECTORY_SEPARATOR.$this->jobId;
    }

    public function tearDown()
    {
        if (\file_exists($this->baseWorkDir)) {
            $fs = new Filesystem();
            $fs->remove($this->baseWorkDir);
        }
    }

    public function assertPreConditions()
    {
        $this->assertDirectoryExists($this->baseWorkDir);
        $this->assertDirectoryNotExists($this->jobWorkDir);
    }

    public function testSetWorkDirCreatesDirectory()
    {
        $mock = $this->getMockTrait();
        $mock->setWorkDir($this->baseWorkDir);
        $this->assertDirectoryExists($this->jobWorkDir);
    }

    public function testSetWorkDirSetParameter()
    {
        $mock = $this->getMockTrait();
        $mock->setWorkDir($this->baseWorkDir);
        $this->assertEquals($this->jobWorkDir, $mock->getWorkDir());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessageRegExp  /Parameter ".+" is not set/
     */
    public function testGetWorkDirThrowsExceptionIfParameterIsNotSet()
    {
        $mock = $this->getMockTrait();
        $mock->getWorkDir();
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Work directory already set
     */
    public function testSetWorkDirThrowsExceptionIfParameterAlreadySet()
    {
        $mock = $this->getMockTrait();
        $mock->setWorkDir($this->baseWorkDir);
        $mock->setWorkDir($this->baseWorkDir);
    }

//    /**
//     * @expectedException \InvalidArgumentException
//     * @expectedExceptionMessage Base work directory MUST exists
//     */
//    public function testSetWorkDirThrowsExceptionIfBaseWorkDoesNotExist()
//    {
//        $fs = new Filesystem();
//        $fs->remove($this->baseWorkDir);
//        $mock = $this->getMockTrait();
//        $mock->setWorkDir($this->baseWorkDir);
//    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Work directory already exists. Cannot create
     */
    public function testSetWorkDirThrowsExceptionIfWorkDirAlreadyExists()
    {
        \mkdir($this->jobWorkDir);
        $mock = $this->getMockTrait();
        $mock->setWorkDir($this->baseWorkDir);
    }
}
