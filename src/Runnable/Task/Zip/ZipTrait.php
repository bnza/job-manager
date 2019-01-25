<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable\Task\Zip;


trait ZipTrait
{
    /**
     * @var \ZipArchive
     */
    protected $zip = [
        'archive' => null,
        'path' => ''
    ];

    public function getZipArchive(string $path = ''): \ZipArchive
    {
        if (!$this->zip['archive']) {
            $this->zip['archive'] = new \ZipArchive();
        };
        if ($path) {
            $this->setZipArchivePath($path);
        }
        return $this->zip['archive'];
    }

    public function setZipArchivePath(string $path): self
    {
        $this->zip['path'] = $path;
        return $this;
    }

    public function getZipArchivePath(): string
    {
        return $this->zip['path'];
    }

    public function getZipArchiveNumFiles(): int
    {
        $zip = $this->getZipArchive();
        $path = $this->getZipArchivePath();
        $res = $zip->open($path);
        if($res === true) {
            $num = $zip->numFiles;
            $zip->close();
            return $num;
        }
        throw new \RuntimeException(sprintf("Failed to open \"%s\": error code [%i]", $path, $res));
    }

    protected function openZipArchive(int $flags = null)
    {
        $zip = $this->getZipArchive();
        $res = $zip->open($this->getZipArchivePath(), $flags);
        if ($res === true) {
            return $zip;
        } else {
            throw new \RuntimeException(sprintf("Failed opening \"%s\". Error code: %i", $this->getZipArchivePath(), $res));
        }
    }

    protected function closeZipArchive()
    {
        $res = $this->getZipArchive()->close();
        if (!$res) {
            throw new \RuntimeException(
                sprintf(
                    "Failed to close \"%s\": %s",
                    $this->getZipArchivePath(),
                    \error_get_last()
                )
            );
        }
    }

    /**
     * @param string $destination
     * @param string $filename
     */
    protected function zipArchiveSingleExtractTo(string $destination, string $filename)
    {
        $res = $this->getZipArchive()->extractTo($destination, [$filename]);
        if (!$res) {
            throw new \RuntimeException(
                sprintf(
                    "Failed to extract file \"%s\" from \"%s\" to \"%s\": %s",
                    $filename,
                    $this->getZipArchivePath(),
                    $destination,
                    \error_get_last()
                )
            );
        }
    }

}
