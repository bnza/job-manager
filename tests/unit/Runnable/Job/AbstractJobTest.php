<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runnable\Job;

use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Bnza\JobManagerBundle\Runnable\Job\AbstractJob;
use Bnza\JobManagerBundle\Runnable\Task\AbstractTask;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Runnable\Job\JobInterface;
use Bnza\JobManagerBundle\Runnable\Status;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\ParameterBag;

class DummyTask extends AbstractTask
{
    public $prop1;
    public $prop2;

    public function __construct(ObjectManagerInterface $om, JobInterface $job, int $num, string $param1, int $param2 = 2)
    {
        parent::__construct($om, $job, $num);
        $this->prop1 = $param1;
        $this->prop2 = $param2;
    }

    public function getName(): string
    {
        return 'DummyTask name';
    }

    public function getSteps(): iterable
    {
        return [
            [$this, 'runMethod'], ['arg0', 'arg1'],
        ];
    }

    public function run(): void
    {
    }
}

class DummyNonTask
{
}

class AbstractJobTest extends \PHPUnit\Framework\TestCase
{

    public function testGetDispatcherWillReturnDispatcher()
    {
        $context = $this->initAbstractJobMockConstructor();


        $this->assertEquals($context['mockDispatcher'], $context['mockJob']->getDispatcher());
    }

    public function testGetParametersWillReturnParameterBag()
    {
        $context = $this->initAbstractJobMockConstructor();

        /**
         * @var AbstractJob
         */
        $mockJob = $context['mockJob'];
        $this->assertInstanceOf(ParameterBag::class, $mockJob->getParameters());
    }

    public function testMethodSuccessWillSetSuccessStatus()
    {
        $context = $this->initAbstractJobMockConstructor();

        $mockJob = $context['mockJob'];
        $mockEntity = $context['mockEntity'];
        $mockOm = $context['mockOm'];
        $mockStatus = $context['mockStatus'];

        $mockJob->method('getSteps')->willReturn([]);
        $mockStatus->expects($this->once())->method('run');
        $mockStatus->expects($this->once())->method('success');
        $mockOm->method('persist')->with($mockEntity, 'status');

        $mockJob->run();
    }

    /**
     * @testdox Method run() call runTask() method with right arguments
     */
    public function testMethodRunCallRunTaskMethodWithRightArguments()
    {
        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['running', 'runTask', 'success', 'getSteps']
        );

        $mockJob->expects($this->once())
            ->method('running');

        $mockJob->expects($this->once())
            ->method('success');

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['class' => 'TaskClass1'],
                ['class' => 'TaskClass2'],
            ]);

        $mockJob->expects($this->any())
            ->method('runTask')
            ->withConsecutive(
                [0, ['class' => 'TaskClass1']],
                [1, ['class' => 'TaskClass2']]
            );

        $mockJob->run();
    }

    /**
     * @param array $taskData
     * @param array $expectedArgs
     *
     * @dataProvider initTaskDataProvider
     */
    public function testCreateTaskIsCalledWithRightArguments(array $taskData, array $expectedArgs)
    {
        $mockJob = $this->initMockJob($taskData, ['createTask']);

        $mockJob->expects($this->once())
            ->method('createTask')
            ->with(
                $this->equalTo(DummyTask::class),
                $this->equalTo(0),
                $this->equalTo($expectedArgs)
            );

        $mockJob->run();
    }

    /**
     * @testdox Method setTaskParameters() set task parameter
     * @param array $taskData
     * @param string $mockedTaskSetter
     * @param array $expectedArgs
     *
     * @throws \ReflectionException
     * @dataProvider setTaskParametersDataProvider
     */
    public function testMethodSetTaskParametersSetTaskParameters(array $taskData, string $mockedTaskSetter, array $expectedArgs)
    {

        $mockTask = $this->getMockForAbstractClass(
            AbstractTask::class,
            [],
            '',
            false,
            false,
            true,
            [$mockedTaskSetter, 'run', 'success']
        );

        $mockTask->expects($this->once())->method($mockedTaskSetter)->with(...$expectedArgs);

        $mockJob = $this->initMockJob($taskData, ['initTask', 'getDummyParameter']);

        $mockJob->expects($this->once())
            ->method('initTask')
            ->willReturn(
                $mockTask
            );

        $mockJob->run();
    }

    /**
     * @testdox Method setJobParameters() set task parameter
     * @param array $taskData
     * @param string $mockedTaskGetter
     * @param string $mockedJobSetter
     * @param array $expectedArgs
     *
     * @throws \ReflectionException
     * @dataProvider setJobParametersDataProvider
     */
    public function testMethodSetJobParametersSetTaskParameters(array $taskData, string $mockedTaskGetter, string $mockedJobSetter, array $expectedArgs)
    {
        $taskMockedMethods = ['run', 'success'];
        if ($mockedTaskGetter) {
            $taskMockedMethods[] = $mockedTaskGetter;
        }
        $mockTask = $this->getMockForAbstractClass(
            AbstractTask::class,
            [],
            '',
            false,
            false,
            true,
            $taskMockedMethods
        );

        if ($mockedTaskGetter) {
            $mockTask->expects($this->once())->method($mockedTaskGetter)->willReturn($expectedArgs[0]);
            $taskData['setters'][0][] = [$mockTask, $mockedTaskGetter];
        }

        $mockJob = $this->initMockJob($taskData, ['initTask', $mockedJobSetter]);

        $mockJob->expects($this->once())
            ->method('initTask')
            ->willReturn(
                $mockTask
            );

        $mockJob->expects($this->once())
            ->method($mockedJobSetter)
            ->with(...$expectedArgs);

        $mockJob->run();
    }

    /**
     * @testdox Method setTaskParameters() set task parameter using job getter
     * @throws \ReflectionException
     */
    public function testMethodSetTaskParametersSetTaskParametersUsingJobGetter()
    {
        $mockedTaskSetter = 'setDummyParameter';

        $par1 = (int) mt_rand(0, 100);

        $taskData = [
            ['class' => DummyTask::class],
            'setDummyParameter',
            [$par1],
        ];

        $mockTask = $this->getMockForAbstractClass(
            AbstractTask::class,
            [],
            '',
            false,
            false,
            true,
            [$mockedTaskSetter, 'run', 'success']
        );

        $mockTask->expects($this->once())->method($mockedTaskSetter)->with($par1);

        $mockJob = $this->initMockJob($taskData, ['initTask', 'getDummyParameter'], false);

        $taskData['parameters'] = [[$mockedTaskSetter, [$mockJob, 'getDummyParameter']]];

        $mockJob->expects($this->once())
            ->method('initTask')
            ->willReturn(
                $mockTask
            );

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                $taskData,
            ]);

        $mockJob->expects($this->once())
            ->method('getDummyParameter')
            ->willReturn(
                $par1
            );

        $mockJob->run();
    }

    /**
     * @param array $taskData
     * @param array $expectedArgs
     *
     * @dataProvider initTaskDataProvider
     */
    public function testCreateTaskWillCreateTaskInstances(array $taskData, array $expectedArgs)
    {
        $mockJob = $this->initMockJob($taskData);
        $mockJob->run();
        $task = $mockJob->getTask(0);
        $this->assertEquals($expectedArgs[0], $task->prop1);
        $this->assertEquals(isset($expectedArgs[1]) ? $expectedArgs[1] : 2, $task->prop2);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Task class must implement TaskInterface:
     */
    public function testInitTaskWithWrongTaskInterfaceImplementationThrowException()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getDispatcher', 'error']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                ['class' => DummyNonTask::class],
            ]);

        $mockJob->run();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dummy exception
     */
    public function testRunTaskExceptionWillCallErrorAndThrowsException()
    {
        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getSteps', 'runTask', 'error', 'running']
        );

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                ['class' => DummyTask::class],
            ]);

        $e = new \Exception("Dummy exception");

        $mockJob->expects($this->once())
            ->method('error')
            ->with($e);

        $mockJob->expects($this->once())
            ->method('runTask')
            ->willThrowException($e);

        $mockJob->run();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dummy exception
     */
    public function testRollback()
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $mockTask = $this->createMock(AbstractTask::class);

        $mockDispatcher = $this->createMock(EventDispatcher::class);

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            ['getObjectManager', 'getEntity', 'getSteps', 'getTasks', 'initTask', 'getDispatcher']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob->expects($this->any())
            ->method('getSteps')
            ->willReturn([
                ['class' => 'TaskClass1'],
                ['class' => 'TaskClass2'],
            ]);

        $mockJob->expects($this->exactly(2))
            ->method('initTask')
            ->willReturn($mockTask);

        $mockJob->expects($this->once())
            ->method('getTasks')
            ->willReturn([$mockTask, $mockTask]);

        $mockJob->expects($this->any())
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $mockTask
            ->expects($this->exactly(2))
            ->method('run')
            ->will(
                $this->onConsecutiveCalls(
                    true,
                    $this->throwException(new \Exception('Dummy exception'))
                )
            );

        $mockTask
            ->expects($this->exactly(2))
            ->method('rollback');

        $mockJob->run();
    }

    public function initMockJob(array $taskData, array $mockedMethods = [], bool $expectGetSteps = true)
    {
        $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStatus = $this->createMock(Status::class);

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        $mockOm = $this->createMock(ObjectManagerInterface::class);

        $defaultMockedMethods = ['getObjectManager', 'getEntity', 'getSteps', 'success', 'running'];

        $methods = \array_merge(
            $defaultMockedMethods,
            $mockedMethods
        );

        $mockJob = $this->getMockForAbstractClass(
            AbstractJob::class,
            [],
            '',
            false,
            true,
            true,
            $methods
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        if ($expectGetSteps) {
            $mockJob->expects($this->once())
                ->method('getSteps')
                ->willReturn([
                    $taskData,
                ]);
        }

        $mockJob->expects($this->once())
            ->method('success');

        return $mockJob;
    }

    public function initAbstractJobMock(
        Status $mockStatus = null,
        JobEntityInterface $mockEntity = null,
        ObjectManagerInterface $mockOm = null,
        EventDispatcher $mockDispatcher = null,
        AbstractTask $mockTask = null,
        AbstractJob $mockJob = null
    ) {
        if (!$mockStatus) {
            $mockStatus = $this->createMock(Status::class);
        }

        if (!$mockEntity) {
            $mockEntity = $this->getMockBuilder(JobEntityInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        if (!$mockOm) {
            $mockOm = $this->createMock(ObjectManagerInterface::class);
        }

        if (!$mockOm) {
            $mockOm = $this->createMock(ObjectManagerInterface::class);
        }

        if (!$mockDispatcher) {
            $mockDispatcher = $this->createMock(EventDispatcher::class);
        }

        if (!$mockTask) {
            $mockTask = $this->createMock(AbstractTask::class);
        }

        if (!$mockJob) {
            $mockJob = $this->getMockForAbstractClass(
                AbstractJob::class,
                [],
                '',
                false,
                true,
                true,
                ['getName', 'getSteps', 'initTask']
            );
        }

        return \compact(
            'mockStatus',
            'mockEntity',
            'mockOm',
            'mockDispatcher',
            'mockTask',
            'mockJob'
        );
    }

    public function initAbstractJobMockConstructor(
        Status $mockStatus = null,
        JobEntityInterface $mockEntity = null,
        ObjectManagerInterface $mockOm = null,
        EventDispatcher $mockDispatcher = null,
        AbstractTask $mockTask = null,
        AbstractJob $mockJob = null
    ) {
        \extract(
            $this->initAbstractJobMock(
                $mockStatus,
                $mockEntity,
                $mockOm,
                $mockDispatcher,
                $mockTask,
                $mockJob
            )
        );

        $reflectedClass = new \ReflectionClass(AbstractJob::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invokeArgs($mockJob, [$mockOm, $mockDispatcher, $mockEntity]);

        return \compact(
            'mockStatus',
            'mockEntity',
            'mockOm',
            'mockDispatcher',
            'mockTask',
            'mockJob'
        );
    }

    public function initTaskDataProvider()
    {
        $arg2 = (int) rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                ['class' => DummyTask::class, 'arguments' => $arg1],
                [$arg1],
            ],
            [
                ['class' => DummyTask::class, 'arguments' => [$arg1, $arg2]],
                [$arg1, $arg2],
            ],
        ];
    }

    public function setTaskParametersDataProvider()
    {
        $arg2 = (int) rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                ['class' => DummyTask::class, 'parameters' => [['setDummyParameter', $arg1]]],
                'setDummyParameter',
                [$arg1],
            ],
            [
                ['class' => DummyTask::class, 'parameters' => [['setDummyParameter', 'sys_get_temp_dir']]],
                'setDummyParameter',
                [sys_get_temp_dir()],
            ]
        ];
    }

    public function setJobParametersDataProvider()
    {
        $arg2 = (int) rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                ['class' => DummyTask::class, 'setters' => [['setDummyParameter', $arg1]]],
                '',
                'setDummyParameter',
                [$arg1],
            ],
            [
                ['class' => DummyTask::class, 'setters' => [['setDummyParameter', 'sys_get_temp_dir']]],
                '',
                'setDummyParameter',
                [sys_get_temp_dir()],
            ],
            [
                ['class' => DummyTask::class, 'setters' => [['setDummyParameter']]],
                'getDummyParameter',
                'setDummyParameter',
                [$arg1],
            ]
        ];
    }
}
