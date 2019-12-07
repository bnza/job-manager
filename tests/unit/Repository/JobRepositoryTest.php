<?php declare(strict_types=1);

namespace Bnza\JobManagerBundle\Tests\Unit\Repository;

use Bnza\JobManagerBundle\Repository\ActiveTaskRepositoryInterface;
use Bnza\JobManagerBundle\Repository\JobRepository;
use Bnza\JobManagerBundle\Repository\TaskRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class JobRepositoryTest extends TestCase
{
    /**
     * @var MockObject|ActiveTaskRepositoryInterface
     */
    private $activeTaskRepo;
    /**
     * @var MockObject|TaskRepositoryInterface
     */
    private $archivedTaskRepo;
    /**
     * @var JobRepository
     */
    private $jobRepo;

    public function setUp(): void
    {
        $this->activeTaskRepo = $this->getMockForAbstractClass(ActiveTaskRepositoryInterface::class);
        $this->archivedTaskRepo = $this->getMockForAbstractClass(TaskRepositoryInterface::class);
        $this->jobRepo = new JobRepository($this->activeTaskRepo, $this->archivedTaskRepo);
    }

    public function existsMethodValuesProvider(): array
    {
        return [
            [true, 0, false, true],
            [false, 1, true, true],
            [false, 1, false, false]
        ];
    }
    /**
     * @dataProvider existsMethodValuesProvider
     */
    public function testMethodExists(bool $activeCallReturn, int $archivedCallCount, bool $archivedCallReturn, bool $expected): void
    {
        $this->activeTaskRepo
            ->expects($this->once())
            ->method('exists')
            ->willReturn($activeCallReturn);

        $this->archivedTaskRepo
            ->expects($this->exactly($archivedCallCount))
            ->method('exists')
            ->willReturn($archivedCallReturn);

        $this->assertEquals($expected, $this->jobRepo->exists('anUuid'));
    }

    /**
     * @dataProvider existsMethodValuesProvider
     */
    public function testMethodUuidExists(bool $isLockedCallReturn, int $existsCallCount, bool $existsCallReturn, bool $expected)
    {
        $this->activeTaskRepo
            ->expects($this->once())
            ->method('isLocked')
            ->willReturn($isLockedCallReturn);
        /**
         *@var MockObject|JobManager $jobManagerMock
         */
        $jobRepoMock = $this
            ->getMockBuilder(JobRepository::class)
            ->setConstructorArgs([$this->activeTaskRepo, $this->archivedTaskRepo])
            ->setMethods(['exists'])
            ->getMock();

        $jobRepoMock
           ->expects($this->exactly($existsCallCount))
           ->method('exists')
           ->willReturn($existsCallReturn);

        $this->assertEquals($expected, $jobRepoMock->existsUuid('anUuid'));
    }

    public function testMethodLock(): void
    {
        $this->activeTaskRepo
            ->expects($this->once())
            ->method('lock')
            ->with('anUuid');
        $this->jobRepo->lock('anUuid');
    }
}
