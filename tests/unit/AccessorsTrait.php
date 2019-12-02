<?php

namespace Bnza\JobManagerBundle\Tests;

use InvalidArgumentException;

trait AccessorsTrait
{
    use CaseConverterTrait;

    private function getByAccessor($object, $property)
    {
        $pascalProperty = self::$caseConverter->convert($property)->toPascal();
        foreach (['get', 'is', 'has'] as $prefix) {
            $method = $prefix.$pascalProperty;
            if (method_exists($object, $method)) {
                return $object->$method();
            }
        }
        throw new InvalidArgumentException(sprintf('No accessor found for property %s in %s class', $property, get_class($object)));
    }

    private function setByAccessor($object, $property, $value)
    {
        $method = 'set'.self::$caseConverter->convert($property)->toPascal();
        if (method_exists($object, $method)) {
            $object->$method($value);
            return $object;
        }
        throw new InvalidArgumentException(sprintf('No accessor found for property %s in %s class', $property, get_class($object)));
    }
}
