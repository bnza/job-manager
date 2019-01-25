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
use Bnza\JobManagerBundle\Tests\UtilsTrait;

class RenameTaskTest extends \PHPUnit\Framework\TestCase
{
    use MockJobUtilsTrait;
    use UtilsTrait;

    /**
     * @var string|string[]
     */
    private $origin;

    /**
     * @var string
     */
    private $target;

    /**
     * @var RenameTask
     */
    private $mockTask;

    public function setUp()
    {
        $this->setUpTestDirectories();
    }

    public function tearDown()
    {
        $this->tearDownTestDirectories();
    }

    public function assertPreConditions()
    {
        $this->assertTestDirectoriesAreEmpty();
    }

    public function testSingleFileOriginWillBeRenamedToFullPathTarget()
    {
        $this->renameSingleFileOriginToFullPathTarget();
        $this->assertSingleFileOriginWillBeRenamed($this->origin, $this->target, $this->mockTask);
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
        $this->renameMultipleFileOriginToDirectoryTarget();
        for ($i = 0; $i < 3; $i++) {
            $this->assertFileNotExists($this->origin[$i]);
            $this->assertFileExists($this->targetDir.DIRECTORY_SEPARATOR.basename($this->origin[$i]));
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

    /**
     * @requires testSingleFileOriginWillBeRenamedToFullPathTarget
     */
    public function testSingleFileOriginRenamedToFullPathTargetWillRollback()
    {
        $this->renameSingleFileOriginToFullPathTarget();
        $this->mockTask->run();
        $this->mockTask->rollback();
        $this->assertFileNotExists($this->target);
        $this->assertFileExists($this->origin);
    }

    /**
     * @requires testMultipleFileOriginWillBeRenamedToDirectoryTarget
     */
    public function testMultipleFileOriginRenamedToDirectoryTargetWillRollback()
    {
        $this->renameMultipleFileOriginToDirectoryTarget();
        $this->mockTask->rollback();
        for ($i = 0; $i < 3; $i++) {
            $this->assertFileExists($this->origin[$i]);
            $this->assertFileNotExists($this->targetDir.DIRECTORY_SEPARATOR.basename($this->origin[$i]));
        }
    }

    protected function getRandomFileName(string $dir) {
        return $dir.DIRECTORY_SEPARATOR.substr(md5(microtime()),0,8);
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

    protected function renameSingleFileOriginToFullPathTarget()
    {
        $origin = $this->origin = $this->getRandomFileName($this->originDir);
        $target = $this->target = $this->targetDir.DIRECTORY_SEPARATOR.'target-file';
        \touch($origin);
        $this->mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [$origin, $target], [], ['next']);
    }

    protected function renameMultipleFileOriginToDirectoryTarget()
    {
        $origins = [];
        for ($i = 0; $i < 3; $i++) {
            $origins[] = $this->getRandomFileName($this->originDir);
            \touch($origins[$i]);
            $this->assertFileExists($origins[$i]);
        }
        $this->origin = $origins;
        $this->mockTask = $this->getMockTaskAndInvokeConstructor(RenameTask::class, [$origins, $this->targetDir], [], ['next']);
        $this->mockTask->run();
    }

}
