<?php
/**
 * Copyright (c) 2019
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

namespace Bnza\JobManagerBundle\Tests\Serializer;

use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Runner\Status;
use Bnza\JobManagerBundle\Serializer\JobConverter;

class JobConverterTest extends \PHPUnit\Framework\TestCase
{
    public function testNormalizer()
    {
        $converter = new JobConverter();
        $jobArr = [
            'Class' => self::class,
            'Name' => 'Runner\\Task name',
            'Status' => new Status(1),
            'CurrentStepNum' => 2,
            'StepsNum' => 3,
            'Error' => 'Bad error',
            'Description' => 'Dummy job description',
            'Message' => 'Dummy job message'
        ];

        $taskArr = [
            'Class' => self::class,
            'Name' => 'Runner\\Task name',
            'Num' => 0,
            'CurrentStepNum' => 2,
            'StepsNum' => 3,
            'Description' => 'Dummy task description',
            'Message' => 'Dummy task message'
        ];

        $job = new JobEntity(sha1('A'));


        foreach ($jobArr as $prop => $value) {
            $job->{"set$prop"}($value);
        }

        $task = new TaskEntity($job);

        foreach ($taskArr as $prop => $value) {
            $task->{"set$prop"}($value);
        }

        $job->addTask($task);

        $this->assertEquals([
            'id' => '6dcd4ce23d88e2ee9568ba546c007c63d9131c1b',
            'status' => [
                'value' => 1,
                'isRunning' => true,
                'isCancelled' => false,
                'isSuccessful' => false,
                'isError' => false
            ],
            'tasks' => [
                0 => [
                    'job' => '6dcd4ce23d88e2ee9568ba546c007c63d9131c1b',
                    'num' => 0,
                    'id' => '6dcd4ce23d88e2ee9568ba546c007c63d9131c1b.0',
                    'status' => [
                        'value' => 1,
                        'isRunning' => true,
                        'isCancelled' => false,
                        'isSuccessful' => false,
                        'isError' => false
                    ],
                    'error' => 'Bad error',
                    'class' => 'Bnza\JobManagerBundle\Tests\Serializer\JobConverterTest',
                    'name' => 'Runner\Task name',
                    'currentStepNum' => 2,
                    'stepsNum' => 3,
                    'description' => 'Dummy task description',
                    'message' => 'Dummy task message'
                ]
            ],
            'error' => 'Bad error',
            'class' => 'Bnza\JobManagerBundle\Tests\Serializer\JobConverterTest',
            'name' => 'Runner\Task name',
            'currentStepNum' => 2,
            'stepsNum' => 3,
            'description' => 'Dummy job description',
            'message' => 'Dummy job message'
        ],
            $converter->normalize($job)
        );
    }
}
