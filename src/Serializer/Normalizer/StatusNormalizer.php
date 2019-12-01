<?php

namespace Bnza\JobManagerBundle\Serializer\Normalizer;

use Bnza\JobManagerBundle\Status\Status;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class StatusNormalizer implements DenormalizerInterface, NormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        return new Status($data['value']);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return Status::class === $type;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return [
            'value' => (int) (string) $object
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Status;
    }
}
