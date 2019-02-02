<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Runner\Job;

use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Tests\MockUtilsTrait;
use Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException;
use Bnza\JobManagerBundle\Runner\Job\AbstractJob;
use Bnza\JobManagerBundle\Runner\Task\AbstractTask;
use Bnza\JobManagerBundle\Runner\Status;
use Bnza\JobManagerBundle\Tests\Fixture\Runner\Task\DummyTask1;
use Symfony\Component\HttpFoundation\ParameterBag;

class AbstractJobTest extends \PHPUnit\Framework\TestCase
{
    use MockUtilsTrait;

    public function testGetDispatcherWillReturnDispatcher()
    {
        $this->getMockAbstractJobWithConstructor();

        $this->assertEquals($this->mockDispatcher, $this->mockJob->getDispatcher());
    }

    public function testGetParametersWillReturnParameterBag()
    {
        $this->getMockAbstractJobWithConstructor();

        $this->assertInstanceOf(ParameterBag::class, $this->mockJob->getParameters());
    }

    public function testMethodSuccessWillSetSuccessStatus()
    {
        $this->getMockStatus(Status::class, ['run', 'success']);
        $this->getMockAbstractJobWithConstructor();

        $this->mockJob->method('getSteps')->willReturn([]);
        $this->mockStatus[0]->expects($this->once())->method('run');
        $this->mockStatus[0]->expects($this->once())->method('success');
        $this->mockOm->method('persist')->with($this->mockJobEntity[0], 'status');

        $this->mockJob->run();
    }

    public function testConstructorWithEmptyEntity()
    {
        $this->getMockAbstractJob(['setUpRunnable', 'getEntity', 'getStepsNum', 'getObjectManager']);
        $this->mockOm->expects($this->once())->method('getEntityClass')->willReturn('JobEntityClassName');
        $this->mockJob->expects($this->once())->method('setUpRunnable')->with($this->anything(), $this->equalTo('JobEntityClassName'));
        $this->mockJob->method('getEntity')->willReturn($this->mockJobEntity[0]);
        $this->mockJob->method('getObjectManager')->willReturn($this->mockOm);
        $this->invokeConstructor(
            AbstractJob::class,
            $this->mockJob,
            [
                $this->mockOm,
                $this->mockDispatcher,
            ]
        );
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage You must must override "getDescription" method in concrete class
     */
    public function testMethodGetDescriptionWillThrowsExcetion()
    {
        $this->getMockJob(AbstractJob::class);
        $this->mockJob->getDescription();
    }

    /**
     * @testdox Method run() call runTask() method with right arguments
     */
    public function testMethodRunCallRunTaskMethodWithRightArguments()
    {
        $mockJob = $this->getMockJob(
            AbstractJob::class,
            ['running', 'runTask', 'success', 'getSteps', 'isCancelled']
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
        $this->getMockJobWithGetStepsMockedMethodSuccess($taskData, ['createTask', 'getArgument1']);

        $this->mockJob->method('getArgument1')->willReturn('Dummy task argument 1');

        $expectedArgs = $this->replacePlaceholderWithMockedObject($expectedArgs);

        $this->mockJob->expects($this->once())
            ->method('createTask')
            ->with(
                $this->equalTo(DummyTask1::class),
                $this->equalTo(0),
                $this->equalTo($expectedArgs)
            );

        $this->mockJob->run();
    }

    /**
     * @dataProvider setWrongTaskDataProvider
     * @param array $taskData
     * @param string $expectedMessage
     */
    public function testMethodInitTaskWillThrowsExceptionWithWrongTaskData(array $taskData, string $expectedMessage)
    {
        $this->getMockJobWithGetStepsMockedMethodError($taskData, ['createTask']);
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage($expectedMessage);
        $this->mockJob->run();
    }

    /**
     * @testdox Method setTaskParameters() set task parameter
     * @param array $taskData
     * @param string $mockedTaskSetter
     * @param array $expectedArgs
     * @param array $expectedException
     * @dataProvider setTaskParametersDataProvider
     */
    public function testMethodSetTaskParametersSetTaskParameters(
        array $taskData,
        string $mockedTaskSetter,
        array $expectedArgs,
        array $expectedException = []
    )
    {
        $mockTask = $this->getMockTask(AbstractTask::class, [$mockedTaskSetter, 'run', 'success']);

        if ($expectedException) {
            $this->expectException($expectedException[0]);
            if ($expectedException[1]) {
                $this->expectExceptionMessage($expectedException[1]);
            }
            if (isset($expectedException[2])) {
                $this->expectExceptionMessageRegExp($expectedException[2]);
            }
            $assertion = 'getMockJobWithGetStepsMockedMethodError';
        } else {
            $mockTask->expects($this->once())->method($mockedTaskSetter)->with(...$expectedArgs);
            $assertion = 'getMockJobWithGetStepsMockedMethodSuccess';
        }

        $mockJob = $this->$assertion($taskData, ['initTask', 'getDummyParameter']);


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
     * @dataProvider setJobParametersDataProvider
     */
    public function testMethodSetJobParametersSetTaskParameters(
        array $taskData,
        string $mockedTaskGetter,
        string $mockedJobSetter,
        array $expectedArgs,
        array $expectedException = []
    )
    {
        $taskMockedMethods = ['run', 'success'];
        if ($mockedTaskGetter) {
            $taskMockedMethods[] = $mockedTaskGetter;
        }

        $mockTask = $this->getMockTask($taskData['class'], $taskMockedMethods);



        if ($expectedException) {
            $this->expectException($expectedException[0]);
            if ($expectedException[1]) {
                $this->expectExceptionMessage($expectedException[1]);
            }
            if (isset($expectedException[2])) {
                $this->expectExceptionMessageRegExp($expectedException[2]);
            }
            $assertion = 'getMockJobWithGetStepsMockedMethodError';
            $expects = $this->never();
        } else {
            if ($mockedTaskGetter) {
                $mockTask->expects($this->once())->method($mockedTaskGetter)->willReturn($expectedArgs[0]);
                $taskData['setters'][0][] = [$mockTask, $mockedTaskGetter];
            }
            $assertion = 'getMockJobWithGetStepsMockedMethodSuccess';
            $expects = $this->once();
        }

        $mockJob = $this->$assertion($taskData, ['initTask', 'jobDummyGetter', $mockedJobSetter]);


        $mockJob->expects($this->once())
            ->method('initTask')
            ->willReturn(
                $mockTask
            );

        $mockJob->expects($expects)
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

        $par1 = (int)mt_rand(0, 100);

        $taskData = [
            ['class' => DummyTask1::class],
            'setDummyParameter',
            [$par1],
        ];

        $mockTask = $this->getMockTask(AbstractTask::class, [$mockedTaskSetter, 'run', 'success']);

        $mockTask->expects($this->once())->method($mockedTaskSetter)->with($par1);

        $mockJob = $this->getMockJobWithGetStepsMockedMethodSuccess($taskData, ['initTask', 'getDummyParameter'], false);

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
    public function testCreateTaskWillCreateTaskInstances(array $taskData, array $expectedArgs, array $taskProps)
    {
        $mockJob = $this->getMockJobWithGetStepsMockedMethodSuccess($taskData, ['getArgument1']);
        $this->mockJob->method('getArgument1')->willReturn('Dummy task argument 1');
        $mockJob->run();
        $task = $mockJob->getTask(0);
        for ($i=0; $i < count($taskProps); $i++) {
            $prop = 'prop'. ($i+1);
            $this->assertEquals($taskProps[$i], $task->$prop);
        }
    }

    /**
     * @param array $taskData
     * @param bool $willRun
     *
     * @param null $getterResult
     * @param array $expectedExceptions
     * @dataProvider checkTaskRunConditionsDataProvider
     */
    public function testMethodCheckTaskRunConditionsWillReturnExpectedValue(
        array $taskData,
        bool $willRun,
        $getterResult = null,
        array $expectedExceptions = []
    )
    {
        $mockJob = $this->getMockJobWithGetStepsMockedMethod($taskData, ['runTask', 'jobGetter', 'success']);
        $expect = $willRun ? $this->once() : $this->never();
        $this->mockJob->expects($expect)->method('runTask');
        if (!\is_null($getterResult)) {
            $this->mockJob->method('jobGetter')->willReturn($getterResult);
        }
        if ($expectedExceptions) {
            $this->expectException($expectedExceptions[0]);
            $this->expectExceptionMessage($expectedExceptions[1]);
        }
        $mockJob->run();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Task class must implement TaskInterface:
     */
    public function testInitTaskWithWrongTaskInterfaceImplementationThrowException()
    {

        $mockStatus = $this->getMockStatus();

        $mockEntity = $this->getMockJobEntity();

        $mockEntity->method('getStatus')->willReturn($mockStatus);

        $mockOm = $this->getMockObjectManager();

        $mockJob = $this->getMockJob(
            AbstractJob::class,
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
                ['class' => \ArrayIterator::class],
            ]);

        $mockJob->run();
    }

    /**
     * @expectedException \Bnza\JobManagerBundle\Exception\JobManagerCancelledJobException
     * @expectedExceptionMessage Job cancelled by user input
     */
    public function testCancelledJobStatusWillThrowException()
    {
        $mockStatus = $this->getMockStatus(Status::class, ['isCancelled']);

        $mockStatus->method('isCancelled')->will(
            $this->onConsecutiveCalls(
                false,
                true
            )
        );

        $mockEntity = $this->getMockJobEntity();

        $mockEntity->method('getStatus')->willReturn($mockStatus);

        $mockOm = $this->getMockObjectManager();

        $mockJob = $this->getMockJob(
            AbstractJob::class,
            ['getObjectManager', 'getEntity', 'runTask', 'success', 'running', 'error']
        );

        $mockJob->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob->expects($this->any())
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob
            ->method('getSteps')
            ->willReturn([
                [],
                []
            ]);

        $mockJob
            ->expects($this->once())
            ->method('error')
            ->with(
                $this->isInstanceOf(JobManagerCancelledJobException::class)
            );

        $mockJob->run();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dummy exception
     */
    public function testRunTaskExceptionWillCallErrorAndThrowsException()
    {
        $mockJob = $this->getMockJob(
            AbstractJob::class,
            ['getSteps', 'runTask', 'error', 'running', 'isCancelled']
        );

        $mockJob->expects($this->once())
            ->method('getSteps')
            ->willReturn([
                [],
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
        $mockStatus = $this->getMockStatus();

        $mockEntity = $this->getMockJobEntity();

        $mockEntity->method('getStatus')->willReturn($mockStatus);

        $mockOm = $this->getMockObjectManager();

        $mockTask = $this->getMockTask(AbstractTask::class, ['run', 'rollback']);

        $mockDispatcher = $this->getMockDispatcher();

        $mockJob = $this->getMockJob(
            AbstractJob::class,
            ['getObjectManager', 'getEntity', 'getSteps', 'getTasks', 'initTask', 'getDispatcher']
        );

        $mockJob
            ->method('getObjectManager')
            ->willReturn($mockOm);

        $mockJob
            ->method('getEntity')
            ->willReturn($mockEntity);

        $mockJob
            ->method('getSteps')
            ->willReturn([
                ['class' => 'TaskClass1'],
                ['class' => 'TaskClass2'],
            ]);

        $mockJob
            ->method('initTask')
            ->willReturn($mockTask);

        $mockJob
            ->method('getTasks')
            ->willReturn([$mockTask, $mockTask]);

        $mockJob
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

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dummy exception
     */
    public function testRollbackException()
    {
        $mockDispatcher = $this->getMockDispatcher();

        $mockJob = $this->getMockJob(
            AbstractJob::class,
            ['configure', 'persistError', 'handleRollBackError', 'rollback', 'running', 'getDispatcher']
        );

        $mockJob
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $e = new \Exception('Rollback error');
        $mockJob->method('configure')->willThrowException(new \Exception('Dummy exception'));
        $mockJob->method('rollback')->willThrowException($e);
        $mockJob->expects($this->once())->method('handleRollBackError')->with($e);
        $mockJob->run();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Dummy exception
     */
    public function testHandleRollbackError()
    {
        $mockDispatcher = $this->getMockDispatcher();

        $mockJob = $this->getMockJob(
            AbstractJob::class,
            ['configure', 'persistError', 'rollback', 'running', 'getDispatcher']
        );

        $mockJob
            ->method('getDispatcher')
            ->willReturn($mockDispatcher);

        $e = new \Exception('Rollback error');
        $mockJob->method('configure')->willThrowException(new \Exception('Dummy exception'));
        $mockJob->method('rollback')->willThrowException($e);
        $mockJob->run();
    }

    public function getMockJobWithGetStepsMockedMethod(array $taskData, array $mockedMethods = [], bool $expectGetSteps = true)
    {
        $defaultMockedMethods = ['getObjectManager', 'getEntity', 'getSteps', 'running', 'getDispatcher'];

        $methods = \array_merge(
            $defaultMockedMethods,
            $mockedMethods
        );

        $this->getMockAbstractJobWithMockedMethods($methods);

        $taskData = $this->replacePlaceholderWithMockedObject($taskData);

        if ($expectGetSteps) {
            $this->mockJob->expects($this->once())
                ->method('getSteps')
                ->willReturn([
                    $taskData,
                ]);
        }

        return $this->mockJob;
    }

    public function getMockJobWithGetStepsMockedMethodSuccess(array $taskData, array $mockedMethods = [], bool $expectGetSteps = true)
    {
        $defaultMockedMethods = ['success'];

        $methods = \array_merge(
            $defaultMockedMethods,
            $mockedMethods
        );

        $this->getMockJobWithGetStepsMockedMethod($taskData, $methods, $expectGetSteps);
        $this->mockJob->expects($this->once())
            ->method('success');

        return $this->mockJob;
    }

    public function getMockJobWithGetStepsMockedMethodError(array $taskData, array $mockedMethods = [], bool $expectGetSteps = true)
    {
        $defaultMockedMethods = ['error'];

        $methods = \array_merge(
            $defaultMockedMethods,
            $mockedMethods
        );

        $this->getMockJobWithGetStepsMockedMethod($taskData, $methods, $expectGetSteps);
        $this->mockJob->expects($this->once())
            ->method('error');

        return $this->mockJob;
    }

    public function getMockAbstractJob(array $jobMockedMethods = [])
    {
        $mockStatus = isset($this->mockStatus[0]) ? $this->mockStatus[0] : $this->getMockStatus();
        $mockEntity = isset($this->mockJobEntity[0]) ? $this->mockJobEntity[0] : $this->getMockJobEntity();
        $this->mockOm ?: $this->getMockObjectManager();
        $this->mockDispatcher ?: $this->getMockDispatcher();

        $mockEntity->method('getStatus')
            ->willReturn($mockStatus);

        //['getName', 'getSteps', 'initTask']
        $defaultJobMockedMethods = ['getName', 'getDescription'];

        $mergedJobMockedMethods = \array_merge(
            $defaultJobMockedMethods,
            $jobMockedMethods
        );

        return $this->getMockJob(
            AbstractJob::class,
            $mergedJobMockedMethods
        );
    }

    public function getMockAbstractJobWithMockedMethods(array $jobMockedMethods = [])
    {
        $this->getMockAbstractJob($jobMockedMethods);

        $this->mockJob->method('getObjectManager')->willReturn($this->mockOm);
        $this->mockJob->method('getEntity')->willReturn($this->mockJobEntity[0]);
        $this->mockJob->method('getDispatcher')->willReturn($this->mockDispatcher);

        return $this->mockJob;

    }

    public function getMockAbstractJobWithConstructor(array $jobMockedMethods = [])
    {
        $this->getMockAbstractJob($jobMockedMethods);
        $this->invokeConstructor(
            AbstractJob::class,
            $this->mockJob,
            [
                $this->mockOm,
                $this->mockDispatcher,
                $this->mockJobEntity[0]
            ]
        );
        return $this->mockJob;
    }

    public function initTaskDataProvider()
    {
        $arg2 = (int)rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                ['class' => DummyTask1::class, 'arguments' => $arg1],
                ['class' => DummyTask1::class, 'arguments' => $arg1],
                [$arg1]
            ],
            [
                ['class' => DummyTask1::class, 'arguments' => [$arg1, $arg2]],
                ['class' => DummyTask1::class, 'arguments' => [$arg1, $arg2]],
                [$arg1, $arg2]
            ],
            [
                ['class' => DummyTask1::class, 'arguments' => [['**mockJob**', 'getArgument1']]],
                ['class' => DummyTask1::class, 'arguments' => [['**mockJob**', 'getArgument1']]],
                ['Dummy task argument 1']
            ]
        ];
    }

    public function setWrongTaskDataProvider()
    {

        return [
            [
                [], "Task class must be provided"

            ],
            [
                ['class' => 0], "Task class must be a string"
            ],
        ];
    }

    public function setTaskParametersDataProvider()
    {
        $arg2 = (int)rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                ['class' => DummyTask1::class, 'parameters' => [['setDummyParameter', $arg1]]],
                'setDummyParameter',
                [$arg1],
            ],
            [
                ['class' => DummyTask1::class, 'parameters' => [['setDummyParameter', 'sys_get_temp_dir']]],
                'setDummyParameter',
                [sys_get_temp_dir()],
            ],
            [
                ['class' => DummyTask1::class, 'parameters' => [['setDummyParameter', 'getName']]],
                'setDummyParameter',
                [''],
            ],
            [
                ['class' => DummyTask1::class, 'parameters' => [['setDummyParameter', ['**mockJob**','getName']]]],
                'setDummyParameter',
                [''],
            ],
            [
                ['class' => DummyTask1::class, 'parameters' => [56]],
                'setDummyParameter',
                [sys_get_temp_dir()],
                [\InvalidArgumentException::class, "Task setter must be an array"]
            ],
            [
                ['class' => DummyTask1::class, 'parameters' => [['nonExistentSetter', $arg1]]],
                'setDummyParameter',
                [''],
                [\InvalidArgumentException::class, "", '/^No "\w+" method found in/']
            ],
        ];
    }

    public function setJobParametersDataProvider()
    {
        $arg2 = (int)rand(1, 100);
        $arg1 = "Dummy string $arg2";

        return [
            [
                ['class' => DummyTask1::class, 'setters' => [['nonExistentSetter', $arg1]]],
                '',
                'setDummyParameter',
                [$arg1],
                [\InvalidArgumentException::class, "", '/^No "\w+" method found in/']
            ],
            [
                ['class' => DummyTask1::class, 'setters' => [['setDummyParameter', $arg1]]],
                '',
                'setDummyParameter',
                [$arg1],
            ],
            [
                ['class' => DummyTask1::class, 'setters' => [['setDummyParameter', 'sys_get_temp_dir']]],
                '',
                'setDummyParameter',
                [sys_get_temp_dir()],
            ],
            [
                ['class' => DummyTask1::class, 'setters' => [['setDummyParameter', ['strtoupper', 'a']]]],
                '',
                'setDummyParameter',
                ['A'],
            ],
            [
                ['class' => DummyTask1::class, 'setters' => [['setDummyParameter', 'getTaskDummyParameter']]],
                'getTaskDummyParameter',
                'setDummyParameter',
                ['A'],
            ],
            [
                ['class' => DummyTask1::class, 'setters' => [['setDummyParameter']]],
                'getDummyParameter',
                'setDummyParameter',
                [$arg1],
            ],
            [
                [
                    'class' => DummyTask1::class,
                    'setters' => [
                        [
                            'setDummyParameter',
                            [
                                'getDummyParameter',
                                ['**mockJob**', 'jobDummyGetter']
                            ]
                        ]
                    ]
                ],
                'getDummyParameter',
                'setDummyParameter',
                ['jobDummyGetter return value'],
            ]
        ];
    }

    public function checkTaskRunConditionsDataProvider()
    {
        return [
            [
                ['class' => DummyTask1::class, 'condition' => true],
                true
            ],
            [
                ['class' => DummyTask1::class, 'condition' => false],
                false
            ],
            [
                ['class' => DummyTask1::class, 'condition' => true, 'negateCondition' => true],
                false
            ],
            [
                ['class' => DummyTask1::class, 'condition' => true, 'negateCondition' => false],
                true
            ],
            [
                ['class' => DummyTask1::class, 'condition' => false],
                false
            ],
            [
                ['class' => DummyTask1::class, 'condition' => 'sys_get_temp_dir'],
                true
            ],
            [
                ['class' => DummyTask1::class, 'condition' => 'jobGetter'],
                true,
                'A string'
            ],
            [
                ['class' => DummyTask1::class, 'condition' => 'jobGetter'],
                true,
                new \DateTime()
            ],
            [
                ['class' => DummyTask1::class, 'condition' => 'jobGetter'],
                false,
                0
            ],
            [
                ['class' => DummyTask1::class, 'condition' => ['jobGetter','A string']],
                false,
                0
            ],
            [
                ['class' => DummyTask1::class, 'condition' => ['jobGetter',['A string', 'Another string']]],
                false,
                0
            ],
            [
                ['class' => DummyTask1::class, 'condition' => [['**mockJob**','jobGetter']]],
                false,
                0
            ],
            [
                ['class' => DummyTask1::class, 'condition' => [['**mockJob**','jobGetter'], 'some value']],
                false,
                0
            ],
            [
                ['class' => DummyTask1::class, 'condition' => ""],
                false,
                0,
                [\InvalidArgumentException::class, "Not boolean falsy values are not allowed"]
            ],
            [
                ['class' => DummyTask1::class, 'condition' => ["not a valid method"]],
                false,
                0,
                [\InvalidArgumentException::class, "Invalid condition method provided:"]
            ],
            [
                ['class' => DummyTask1::class, 'condition' => 35],
                false,
                0,
                [\InvalidArgumentException::class, "Invalid condition provided:"]
            ],
        ];
    }
}
