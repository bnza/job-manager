<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Task\Zip;

use Bnza\JobManagerBundle\ObjectManager\TmpFS\ObjectManager;
use Bnza\JobManagerBundle\Runner\Task\Zip\ZipExtractToTask;
use Bnza\JobManagerBundle\Tests\UtilsTrait;
use Bnza\JobManagerBundle\Tests\Runner\Task\CommonTaskTestTrait;

class ZipExtractToTaskTest extends \PHPUnit\Framework\TestCase
{
    use UtilsTrait;
    use CommonTaskTestTrait;

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

    public function testZipExtractToTaskWillCountTheRightStepsNum()
    {
        $path = $this->copyZipFromAssetsToOriginDir('test1.zip');
        $mockTask = $this->getMockTaskAndInvokeConstructor(ZipExtractToTask::class, [$path, $this->targetDir], [], ['next']);
        $this->assertEquals(3, $mockTask->getStepsNum());
    }

    public function testZipArchiveWillExtractToDestination()
    {
        $path = $this->copyZipFromAssetsToOriginDir('test1.zip');
        $mockTask = $this->getMockTaskAndInvokeConstructor(ZipExtractToTask::class, [$path, $this->targetDir], [], ['next', 'isCancelled']);
        $mockTask->run();
        $this->assertEquals(3, $mockTask->getStepsNum());
    }

    protected function getClassName(): string
    {
        return ZipExtractToTask::class;
    }

    protected function getTaskName(): string
    {
        return 'bnza:task:zip:extract-to';
    }
}
