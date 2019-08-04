<?php
/**
 * Copyright (c) 2019.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Serializer\Normalizer;

use Bnza\JobManagerBundle\Runner\Status;
use Bnza\JobManagerBundle\Serializer\Normalizer\StatusNormalizer;
use Symfony\Component\Serializer\Serializer;

class StatusNormalizerTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalizer()
    {
        $serializer = new Serializer([new StatusNormalizer()]);
        $status = new Status();

        $this->assertEquals([
            'value' => 0,
            'isRunning' => false,
            'isCancelled' => false,
            'isSuccessful' => false,
            'isError' => false,
        ],
            $serializer->normalize($status)
        );
    }
}
