<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Summary\Entry;

use Bnza\JobManagerBundle\Info\InfoInterface;

abstract class AbstractInfoEntry
{
    /**
     * @var InfoInterface
     */
    protected $info;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $context;

    abstract public function getLevel(): string;

    public function __construct(InfoInterface $info, $message, array $context = [])
    {
        $this->info = $info;
        $this->message = $message;
        $this->context = $context;
    }

    public function getInfo(): InfoInterface
    {
        return $this->info;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function asArray(): array
    {
        return [
            'level' => $this->getLevel(),
            'message' => $this->getMessage(),
            'context' => $this->getContext()
        ];
    }
}
