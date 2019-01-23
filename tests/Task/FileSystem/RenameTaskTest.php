<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Task\FileSystem;

use Bnza\JobManagerBundle\Task\FileSystem\RenameTask;
use Bnza\JobManagerBundle\Tests\Task\MockJobUtilsTrait;
use Symfony\Component\Filesystem\Filesystem;

class RenameTaskTest extends \PHPUnit\Framework\TestCase
{
    use MockJobUtilsTrait;

    /**
     * @var string
     */
    private $originDir;

    /**
     * @var string
     */
    private $targetDir;

    /**
     * @var Filesystem
     */
    private $fs;

    protected function getRandomFileName(string $dir) {
        return $dir.DIRECTORY_SEPARATOR.substr(md5(microtime()),0,8);
    }

    public function setUp()
    {
        $this->fs = new Filesystem();
        $this->originDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-origin-dir';
        $this->targetDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test-target-dir';
        foreach ([$this->originDir, $this->targetDir] as $dir) {
            if (file_exists($dir)) {
                $this->fs->remove($dir);
            }
            \mkdir($dir);
        }
    }

    public function tearDown()
    {
        foreach ([$this->originDir, $this->targetDir] as $dir) {
            if (file_exists($dir)) {
                $this->fs->remove($dir);
            }
        }
    }

    public function assertPreConditions()
    {
        foreach ([$this->originDir, $this->targetDir] as $dir) {
            $this->fileExists($dir);
            $this->assertEquals(0, count(glob("$dir/*")));
        }
    }

    protected function assertSingleFileOriginWillBeRenamed($origin, $target, RenameTask $mockTask)
    {
        $this->assertFileExists($origin);
        if ($target !== $this->targetDir) {
            $this->assertFileNotExists($target);
        }
        $mockTask->run();
        $this->assertFileNotExists($origin);
        $this->assertFileExists($target);
    }

    public function testSingleFileOriginWillBeRenamedToFullPathTarget()
    {
        $origin = $this->getRandomFileName($this->originDir);
        $target = $this->targetDir.DIRECTORY_SEPARATOR.'target-file';
        \touch($origin);
        $mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [$origin, $target], [], ['next']);
        $this->assertSingleFileOriginWillBeRenamed($origin, $target, $mockTask);
    }

    public function testSingleFileOriginWillBeRenamedToDirectoryTarget()
    {
        $origin = $this->getRandomFileName($this->originDir);
        \touch($origin);
        $mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [$origin, $this->targetDir], [], ['next']);
        $this->assertSingleFileOriginWillBeRenamed($origin, $this->targetDir.DIRECTORY_SEPARATOR.basename($origin), $mockTask);
    }

    public function testMultipleFileOriginWillBeRenamedToDirectoryTarget()
    {
        $origins = [];
        for ($i = 0; $i < 3; $i++) {
            $origins[] = $this->getRandomFileName($this->originDir);
            \touch($origins[$i]);
            $this->assertFileExists($origins[$i]);
        }

        $mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [$origins, $this->targetDir], [], ['next']);
        $mockTask->run();
        for ($i = 0; $i < 3; $i++) {
            $this->assertFileNotExists($origins[$i]);
            $this->assertFileExists($this->targetDir.DIRECTORY_SEPARATOR.basename($origins[$i]));
        }
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage When you provide multiple origins target must be a directory
     */
    public function testMultipleFileOriginWillBeRenamedToFullPathTargetThrowsException()
    {
        $origins = [];
        for ($i = 0; $i < 3; $i++) {
            $origins[] = $this->getRandomFileName($this->originDir);
            \touch($origins[$i]);
        }
        $target = $this->targetDir.DIRECTORY_SEPARATOR.'target-file';
        $mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [$origins, $target], [], ['next']);
        $mockTask->run();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid origin type:
     */
    public function testInvalidTargetWillThrowsException()
    {
        $target = $this->targetDir.DIRECTORY_SEPARATOR.'target-file';
        $mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [1000, $target], [], ['next']);
        $mockTask->run();
    }


}
