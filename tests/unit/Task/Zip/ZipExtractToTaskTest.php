<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Task\Zip;

use Bnza\JobManagerBundle\Task\Zip\ZipExtractToTask;
use Bnza\JobManagerBundle\Tests\Task\MockJobUtilsTrait;
use Bnza\JobManagerBundle\Tests\UtilsTrait;

class ZipExtractToTaskTest extends \PHPUnit\Framework\TestCase
{
    use UtilsTrait;
    use MockJobUtilsTrait;

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
        $mockTask = $this->getMockTaskAndInvokeConstructor(ZipExtractToTask::class, [$path, $this->targetDir], [], ['next']);
        $mockTask->run();
        $this->assertEquals(3, $mockTask->getStepsNum());
    }

    /*public function testSingleFileOriginWillBeRenamedToFullPathTarget()
    {
        $entries = ['dummy-file1', 'dir1/dir2/dummy-file2'];
        $this->createFiles($entries);
        $this->assertCreatedFilesExist($entries);
    }

    protected function assertCreatedFilesExist(array $entries, string $basePath = '')
    {
        $basePath = $basePath ?: $this->originDir;

        foreach ($entries as $entry) {
            $this->assertFileExists($basePath.DIRECTORY_SEPARATOR.$entry);
        }
    }

    protected function createFiles(array $entries, string $basePath = '')
    {
        $basePath = $basePath ?: $this->originDir;

        foreach ($entries as $entry) {
            $chunks = explode(DIRECTORY_SEPARATOR, $entry);
            $length = \count($chunks);
            $subPath = '';
            for ($i = 0; $i < $length; $i++) {
                if ($i < $length - 1) {
                    $subPath .= $chunks[$i].DIRECTORY_SEPARATOR;
                } else {
                    if ($subPath) {
                        \mkdir($basePath.DIRECTORY_SEPARATOR.$subPath, 0700, true);
                    }
                    \touch($basePath.DIRECTORY_SEPARATOR.$subPath.$chunks[$i]);
                    $subPath = '';
                }
            }
        }
    }*/

}
