<?php

namespace Bnza\JobManagerBundle\Tests\Unit\Serializer\Normalizer;

use Bnza\JobManagerBundle\Serializer\Normalizer\StatusNormalizer;
use Bnza\JobManagerBundle\Status\Status;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Serializer;

class StatusNormalizerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * undocumented function
     *
     * @return void
     */
    public function setUp(): void
    {
        $normalizer = new StatusNormalizer();
        $this->serializer = new Serializer([$normalizer]);
    }

    public function testNormalizer()
    {
        $value = mt_rand(0, 255);
        $status = new Status($value);
        $data = $this->serializer->normalize($status);
        $this->assertArrayHasKey('value', $data);
        $this->assertEquals($value, $data['value']);
    }

    public function testDenormalizer()
    {
        $data = ['value' => mt_rand(0, 255)];
        $status = $this->serializer->denormalize($data, Status::class);
        $this->assertInstanceOf(Status::class, $status);
        $this->assertEquals((string) $data['value'], (string) $status);
    }
}
