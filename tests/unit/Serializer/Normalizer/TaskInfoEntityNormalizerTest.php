<?php

namespace Bnza\JobManagerBundle\Tests\Unit\Serializer\Normalizer;

use Bnza\JobManagerBundle\Entity\TaskInfoEntity;
use Bnza\JobManagerBundle\Serializer\Normalizer\StatusNormalizer;
use Bnza\JobManagerBundle\Serializer\Normalizer\TaskInfoEntityNormalizer;
use Bnza\JobManagerBundle\Status\Status;
use Bnza\JobManagerBundle\Tests\Unit\AccessorsTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class TaskInfoEntityNormalizerTest extends TestCase
{
    use AccessorsTrait;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::setupCaseConverter();
    }

    public function setUp(): void
    {
        $taskInfoNormalizer = new TaskInfoEntityNormalizer();
        $propertyNormalizer = new PropertyNormalizer(null, new CamelCaseToSnakeCaseNameConverter());
        $statusNormalizer = new StatusNormalizer();
        $this->serializer = new Serializer([$taskInfoNormalizer, $statusNormalizer, $propertyNormalizer]);
    }

    public function testDenormalizer()
    {
        $data = $this->getData();
        $info = $this->serializer->denormalize($data, TaskInfoEntity::class);
        $this->assertData($data, $info);
    }

    /**
     */
    public function testDenormalizerWithNestedData()
    {
        $data = $this->getNestedData();
        $info = $this->serializer->denormalize($data, TaskInfoEntity::class);
        $this->assertData($data, $info);
    }
    /**
     * @depends testDenormalizer
     */
    public function testNormalizer()
    {
        $data = $this->getData();
        $info = $this->serializer->denormalize($data, TaskInfoEntity::class);
        $this->assertEquals($data, $this->serializer->normalize($info));
    }

    private function getData(): array
    {
        return [
            'uuid' => sha1(microtime()),
            'class' => self::class,
            'started_at' => microtime(true),
            'finished_at' => null,
            'description' => bin2hex(random_bytes(5)),
            'status' => [
                'value' => mt_rand(0, 255)
            ],
            'steps' => [],
            'current_step_index' => 5,
            'steps_count' => 10
        ];
    }

    private function getNestedData(): array
    {
        $data = $this->getData();
        $nestedData1 = $this->getData();
        $nestedData2 = $this->getData();
        $nestedData2['steps'][] = $this->getData();
        $data['steps'][] = $nestedData1;
        $data['steps'][] = $nestedData2;
        return $data;
    }

    private function assertData(array $data, TaskInfoEntity $info)
    {
        foreach ($data as $key => $value) {
            if ($key === 'status') {
                $this->assertInstanceOf(Status::class, $info->getStatus());
                $this->assertEquals((string) $data['status']['value'], (string) $info->getStatus());
                continue;
            }
            if ($key === 'steps') {
                $stepsObj = $info->getSteps();
                foreach ($value as $i => $stepData) {
                    $this->assertArrayHasKey($i, $stepsObj);
                    $this->assertData($stepData, $stepsObj[$i]);
                }
                continue;
            }
            $this->assertEquals($value, $this->getByAccessor($info, $key));
        }
    }
}
