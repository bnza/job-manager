<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runnable\Task\Zip;


use Bnza\JobManagerBundle\Runnable\Job\JobInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runnable\Task\AbstractTask;

class ZipExtractToTask extends AbstractTask
{
    use ZipTrait;

    /**
     * @var string
     */
    private $destination;

    /**
     * @var array
     */
    private $entries = [];

    public function getName(): string
    {
        return 'bnza:task:zip:extract-to';
    }

    public function __construct(
        ObjectManagerInterface $om,
        JobInterface $job,
        int $num,
        string $path,
        string $destination,
        $entries = []
    )
    {
        $this->destination = $destination;
        $this->setZipArchivePath($path);
        if ($entries) {
            $this->setEntries($entries);
        }
        parent::__construct($om, $job, $num);
    }

    /**
     * @return string
     */
    public function getDestination(): string
    {
        return $this->destination;
    }

    /**
     * @return array
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param array $entries
     */
    public function setEntries($entries): void
    {
        if (\is_string($entries)) {
            $this->entries = [$entries];
        }
        if (\is_array($this->entries)) {
            $this->entries = $entries;
        }
        throw new \InvalidArgumentException("Entries must be a string or an array: ".gettype($entries). " given");
    }

    public function configure(): void
    {
        $this->openZipArchive();
    }

    protected function terminate(): void
    {
        $this->closeZipArchive();
    }

    public function getSteps(): iterable
    {
        $destination = $this->getDestination();
        if ($this->getEntries()) {
            $generator = function ($destination) {
                $entries = $this->getEntries();
                foreach ($entries as $entry) {
                    yield [
                        [$this, 'zipArchiveSingleExtractTo'],
                        [$destination, $entry],
                    ];
                }
            };
        } else {
            $generator = function ($destination) {
                $num = $this->getZipArchiveNumFiles();
                $zip = $this->openZipArchive();
                for($i = 0; $i < $num; $i++) {
                    $entry = $zip->getNameIndex($i);
                    yield [
                        [$this, 'zipArchiveSingleExtractTo'],
                        [$destination, $entry],
                    ];
                }
            };
        }
        return $generator($destination);
    }
}
