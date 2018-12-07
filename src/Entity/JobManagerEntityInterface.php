<?php
/**
 * Copyright (c) 2018
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Entity;

interface JobManagerEntityInterface
{
    public function getClass(): string;

    public function getName(): string;

    public function getCurrentStepNum(): int;

    public function getStepsNum(): int;
}
