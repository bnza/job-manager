<?php

namespace Bnza\JobManagerBundle\Entity;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TaskInfoEntity implements TaskInfoEntityInterface
{
    use TaskInfoEntityTrait;
}
