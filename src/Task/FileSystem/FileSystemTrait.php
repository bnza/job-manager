<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Task\FileSystem;
use Symfony\Component\Filesystem\Filesystem;

trait FileSystemTrait
{
    /**
     * @var Filesystem;
     */
    protected $fs;

    protected function getFileSystem(): Filesystem
    {
        if (!$this->fs) {
            $this->fs = new Filesystem();
        }
        return $this->fs;
    }


}
