<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner;


use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

trait JobFactoryTrait
{
    /**
     * @var ObjectManagerInterface
     */
    private $om;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(ObjectManagerInterface $om, EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $this->om = $om;
    }

    public function getObjectManager(): ObjectManagerInterface
    {
        return $this->om;
    }

    public function getDispatcher(): EventDispatcherInterface
    {
        return $this->dispatcher;
    }
}
