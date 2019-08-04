<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Serializer;

use Bnza\JobManagerBundle\Serializer\Normalizer\StatusNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JobConverter
{
    private $serializer;

    public function getSerializer(): Serializer
    {
        return $this->serializer;
    }

    public function __construct()
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $normalizers = [new StatusNormalizer(), new ObjectNormalizer(null, null, null, null, null, null, $defaultContext)];
        $this->serializer = new Serializer($normalizers);
    }

    public function normalize($data, $format = null, array $context = [])
    {
        return $this->getSerializer()->normalize($data, $format, $context);
    }
}
