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

    /**
     * @return ParameterBag
     */
    public function getParameters(): ParameterBag
    {
        return $this->parameters;
    }

    /**
     * Get the parameter with the given key. By default will throw \LogicException if bag has no such key
     *
     * @param string $key
     * @param bool $throw
     * @return mixed|null
     * @throws \LogicException
     */
    public function getParameter(string $key, bool $throw = true)
    {
        $pb = $this->getParameters();
        if ($pb->has($key)) {
            return $pb->get($key);
        } else if ($throw) {
            throw new \LogicException("Parameter \"$key\" is not set");
        }
        return null;
    }
}
