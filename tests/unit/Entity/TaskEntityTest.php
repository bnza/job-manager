<?php

namespace Bnza\JobManagerBundle\Tests\Unit\Entity;

use Bnza\JobManagerBundle\Entity\TaskEntity;
use Bnza\JobManagerBundle\Status\Status;
use Bnza\JobManagerBundle\Tests\Unit\AccessorsTrait;
use PHPUnit\Framework\TestCase;

class TaskEntityTest extends TestCase
{
    use AccessorsTrait;

    /**
     * @vat TaskEntity
     */
    private $taskEntity;

    /**
     * undocumented function
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::setupCaseConverter();
    }

    public function setUp(): void
    {
        $this->taskEntity = new TaskEntity();
    }

    public function propertyValuesProvider(): array
    {
        return [
            ['id' , sha1(microtime())],
            ['started_at' , microtime(true)],
            ['finished_at' , microtime(true) + 10],
            ['status' , new Status(mt_rand(0, 255))],
            ['class' , self::class.mt_rand(0, 255)],
            ['description' , bin2hex(random_bytes(10))]
        ];
    }

    /**
     * @dataProvider  propertyValuesProvider
     */
    public function testSetters(string $property, $value)
    {
        $this->setByAccessor($this->taskEntity, $property, $value);
        $this->assertEquals($value, $this->getByAccessor($this->taskEntity, $property));
    }
}
