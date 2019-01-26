<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Runner\Job;

use Symfony\Component\HttpFoundation\ParameterBag;

trait ParameterBagTrait
{
    /**
     * @var ParameterBag
     */
    protected $parameters;

    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    public function getParameter(string $key, bool $throw = true)
    {
        $pb = $this->getParameters();
        if ($pb->has($key)) {
            return $pb->get($key);
        }
        if ($throw) {
            throw new \LogicException("Parameter \"$key\" is not set");
        }
    }


}
