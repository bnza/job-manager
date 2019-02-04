<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Job;

trait WorkDirTrait
{

    use ParameterBagTrait;

    /**
     * @link \Bnza\JobManagerBundle\Runnar\RunnableInfoInterface
     * @return string
     */
    abstract function getId(): string;

    private function getParameterKey(): string
    {
        return "work-dir";
    }

    public function setWorkDir(string $baseWorkDir)
    {
        $key = $this->getParameterKey();
        $pb = $this->getParameters();
        if ($pb->has($key)) {
            throw new \LogicException("Work directory already set");
        }
        $pb->set($key, $this->createWorkDir($baseWorkDir));
    }

    public function getWorkDir(bool $throw = true): string
    {
        return $this->getParameter($this->getParameterKey(), $throw);
    }

    /**
     * @param string $baseWorkDir
     * @return string
     */
    protected function createWorkDir(string $baseWorkDir): string
    {
        if (!\file_exists($baseWorkDir)) {
            \mkdir($baseWorkDir,0700, true);
        }
        $workDir = $baseWorkDir.DIRECTORY_SEPARATOR.$this->getId();
        if (\file_exists($workDir)) {
            throw new \RuntimeException("Work directory already exists. Cannot create");
        }
        \mkdir($workDir, 0700);
        return realpath($workDir);
    }
}
