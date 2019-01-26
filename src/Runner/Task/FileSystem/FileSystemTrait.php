<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Task\FileSystem;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Trait FileSystemTrait
 * Trait used by tasks which need Symfony\Component\Filesystem\Filesystem
 * @package Bnza\JobManagerBundle\Task\FileSystem
 */
trait FileSystemTrait
{
    /**
     * @var Filesystem;
     */
    protected $fs;

    /**
     * @return Filesystem
     */
    protected function getFileSystem(): Filesystem
    {
        if (!$this->fs) {
            $this->fs = new Filesystem();
        }
        return $this->fs;
    }


}
