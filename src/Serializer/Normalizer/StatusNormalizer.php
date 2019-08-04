<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Serializer\Normalizer;

use Bnza\JobManagerBundle\Runner\Status;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StatusNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [];
        $data['value'] = $object->get();
        $data['isRunning'] = $object->isRunning();
        $data['isCancelled'] = $object->isCancelled();
        $data['isSuccessful'] = $object->isSuccessful();
        $data['isError'] = $object->isError();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Status;
    }
}
