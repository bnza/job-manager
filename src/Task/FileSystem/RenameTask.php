<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Task\FileSystem;

use Bnza\JobManagerBundle\Job\JobInterface;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Task\AbstractTask;

class RenameTask extends AbstractTask
{
    use FileSystemTrait;

    /**
     * @var mixed
     */
    private $origin;

    /**
     * @var string
     */
    private $target;

    /**
     * @var bool
     */
    private $overwrite = false;

    public function getName(): string
    {
        return 'bnza:task:filesystem:rename';
    }

    /**
     * {@inheritdoc}
     */
    public function getSteps(): iterable
    {
        $overwrite = $this->isOverwrite();
        $origins = $this->getOrigin();
        if (count($origins) > 1 && !\is_dir($this->getTarget())) {
            throw new \InvalidArgumentException("When you provide multiple origins target must be a directory");
        }
        $generator = function () use ($origins, $overwrite){
            foreach ($origins as $origin) {
                yield [
                    [$this, 'rename'],
                    [$origin, $this->getTarget($origin), $overwrite],
                ];
            }
        };

        return $generator();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): void
    {
        $origins = $this->getOrigin();
        if (count($origins) > 1) {
            if (\is_dir($target = $this->getTarget())) {
                foreach ($origins as $origin) {
                    $this->rename($this->getTarget().DIRECTORY_SEPARATOR.basename($origin), $origin, false);
                }
            }
        } else {
            $this->rename($this->getTarget($origins[0]), $origins[0], false);
        }
    }

    /**
     * Renames/moves files/directories using Symfony/FileSystem
     * @param string $origin
     * @param string $target
     * @param bool $overwrite
     */
    public function rename(string $origin, string $target, bool $overwrite = false)
    {
        $this->getFileSystem()->rename($origin, $target, $overwrite);
    }

    /**
     * RenameTask constructor.
     * @param ObjectManagerInterface $om
     * @param JobInterface $job
     * @param int $num
     * @param $source
     * @param string $target
     * @param bool $overwrite
     * @throws \Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException
     */
    public function __construct(
        ObjectManagerInterface $om,
        JobInterface $job,
        int $num,
        $source,
        string $target,
        bool $overwrite = false
    ) {
        $this->origin = $source;
        $this->target = $target;
        $this->overwrite = $overwrite;
        parent::__construct($om, $job, $num);
    }

    /**
     * @return iterable
     */
    public function getOrigin(): iterable
    {
        if (!\is_iterable($this->origin)) {
            if (\is_string($this->origin)) {
                $this->origin = [$this->origin];
            } else {
                throw new \InvalidArgumentException('Invalid origin type: '.gettype($this->origin));
            }
        }

        return $this->origin;
    }

    /**
     * When $origin argument is provided then if $target is a directory and $origin is file than the returned value will
     * be a new path with the target dirname and the $origin basename.
     *
     * @param string $origin
     *
     * @return string
     */
    public function getTarget(string $origin = ''): string
    {
        if ($origin && \is_file($origin)) {
            if (\is_dir($this->target)) {
                return $this->target.DIRECTORY_SEPARATOR.basename($origin);
            }
        }

        return $this->target;
    }

    /**
     * @return bool
     */
    public function isOverwrite(): bool
    {
        return $this->overwrite;
    }
}
