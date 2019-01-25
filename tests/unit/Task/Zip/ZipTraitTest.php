<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Task\Zip;

use Bnza\JobManagerBundle\Task\Zip\ZipTrait;
use Bnza\JobManagerBundle\Tests\UtilsTrait;

class ZipTraitTest extends \PHPUnit\Framework\TestCase
{
    use UtilsTrait;

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

    public function testGetZipArchiveWillReturnsZipArchive()
    {
        $trait = $this->getObjectForTrait(ZipTrait::class);
        $this->assertInstanceOf(\ZipArchive::class, $trait->getZipArchive());
    }

    public function testSetZipArchivePathDoesWork()
    {
        $basename = 'test1.zip';
        $fullname = $this->originDir.DIRECTORY_SEPARATOR.$basename;
        $trait = $this->getZipTraitMockAndSetZipArchivePath($basename);
        $this->assertEquals($fullname, $trait->getZipArchivePath());

    }

    public function testGetZipArchiveNumFilesDoesWork()
    {
        $trait = $this->getZipTraitMockAndSetZipArchivePath('test1.zip');
        $this->assertEquals(3, $trait->getZipArchiveNumFiles());

    }

    protected function getZipTraitMockAndSetZipArchivePath(string $basename)
    {
        $fullname = $this->originDir.DIRECTORY_SEPARATOR.$basename;
        $this->copyZipFromAssetsToOriginDir('test1.zip');
        $trait = $this->getObjectForTrait(ZipTrait::class);
        $trait->setZipArchivePath($fullname);
        return $trait;
    }
}
