<?php

namespace Bnza\JobManagerBundle\Serializer\Normalizer;

use Bnza\JobManagerBundle\Entity\TaskInfoEntity;
use Bnza\JobManagerBundle\Status\Status;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

class TaskInfoEntityNormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;

    const SKIP = 'skip';

    private function dataHasStatusArray($data): bool
    {
        return array_key_exists('status', $data) && is_array($data['status']);
    }

    /**
     * {@inheritDoc}
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if ($this->dataHasStatusArray($data)) {
            $data['status'] = $this->denormalizer->denormalize($data['status'], Status::class, $format, $context);
        }
        $steps = [];
        foreach ($data['steps'] as $step) {
            $steps[] = $this->denormalizer->denormalize($step, $type, $format, $context);
        }
        $data['steps'] = $steps;
        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        // When $data['status'] is already denormalized skip to PropertyNormalizer
        return $type === TaskInfoEntity::class && $this->dataHasStatusArray($data);
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        // Skip first to PropertyNormalizer
        $data = $this->normalizer->normalize($object, self::SKIP, $context);
        $data['status'] = $this->normalizer->normalize($data['status'], Status::class, $context);
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TaskInfoEntity && $format !== self::SKIP;
    }
}
