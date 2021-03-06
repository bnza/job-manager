<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity\TmpFS;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;

abstract class AbstractRunnableEntity implements RunnableEntityInterface
{
    /**
     * @var string
     */
    protected $class = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var int
     */
    protected $currentStepNum = 0;

    /**
     * @var int
     */
    protected $stepsNum = 0;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $message = '';

    public function getClass(): string
    {
        return $this->class;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCurrentStepNum(): int
    {
        return $this->currentStepNum;
    }

    public function getStepsNum(): int
    {
        return $this->stepsNum;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}
