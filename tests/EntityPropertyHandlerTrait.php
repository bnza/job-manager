<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests;

use Doctrine\Common\Inflector\Inflector;
use Bnza\JobManagerBundle\Entity\JobManagerEntityInterface;

trait EntityPropertyHandlerTrait
{
    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @return Inflector
     */
    private function getPropertyInflector(): Inflector
    {
        if (!$this->inflector) {
            $this->inflector = new Inflector();
        }

        return $this->inflector;
    }

    private function getPropertyInflectedMethod(string $action, string $prop): string
    {
        return $action.$this->getPropertyInflector()->classify($prop);
    }

    /**
     * @param JobManagerEntityInterface $entity
     * @param string                    $action
     * @param string                    $prop
     * @param bool                      $value
     *
     * @return mixed
     */
    private function handleEntityProp(JobManagerEntityInterface $entity, string $action, string $prop, $value = false)
    {
        $method = $this->getPropertyInflectedMethod($action, $prop);
        if ('set' == $action) {
            $entity->$method($value);
        } elseif ('get' == $action) {
            return $entity->$method();
        }
    }
}
