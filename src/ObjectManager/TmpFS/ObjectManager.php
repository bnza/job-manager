<?php
/**
 * Copyright (c) 2018.
 *
 * Author: Pietro Baldassarri
 *
 * For full license information see the README.md file
 */

/**
 * Created by PhpStorm.
 * User: petrux
 * Date: 30/11/18
 * Time: 15.34.
 */

namespace Bnza\JobManagerBundle\ObjectManager\TmpFS;

use Bnza\JobManagerBundle\Entity\RunnableEntityInterface;
use Bnza\JobManagerBundle\Entity\JobEntityInterface;
use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Entity\TmpFS\JobEntity;
use Bnza\JobManagerBundle\Entity\TmpFS\TaskEntity;
use Bnza\JobManagerBundle\Exception\JobManagerEntityNotFoundException;
use Bnza\JobManagerBundle\ObjectManager\ObjectManagerInterface;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\DependencyInjection\Tests\Compiler\F;
use Symfony\Component\Filesystem\Filesystem;

class ObjectManager implements ObjectManagerInterface
{
    /**
     * @var string
     */
    private $basePath = '';

    private $workDir = '';

    /**
     * @var Filesystem
     */
    private $fs;

    /**
     * @var Inflector
     */
    private $inflector;

    /**
     * @var array
     */
    private $jobPropertiesList = [
        'status',
        'class',
        'name',
        'current_step_num',
        'steps_num',
        'error',
        'description',
        'message'
    ];

    /**
     * @var array
     */
    private $taskPropertiesList = [
        'class',
        'steps_num',
        'name',
        'current_step_num',
        'description',
        'message'
    ];

    /**
     * ObjectManager constructor.
     * @param string $env
     * @param string $tempDir The tmpfs tmp dir
     * @param string $workDir The job's work dir, used as archive to not pollute memory
     */
    public function __construct(string $env = 'dev', string $tempDir = '', string $workDir='')
    {
        if ($tempDir) {
            if (file_exists($tempDir)) {
                if (is_readable($tempDir)) {
                    if (is_writable($tempDir)) {
                        $path = $tempDir;
                    } else {
                        throw new \InvalidArgumentException("$tempDir is not writable");
                    }
                } else {
                    throw new \InvalidArgumentException("$tempDir is not readable");
                }
            } else {
                throw new \InvalidArgumentException("$tempDir does not exist");
            }
        } else {
            $path = \sys_get_temp_dir();
        }

        $this->basePath = \implode(
            DIRECTORY_SEPARATOR,
            [
                $path,
                'bnza',
                $env,
                'job_manager',
                'jobs',
            ]
        );

        $this->inflector = new Inflector();

        $this->workDir = $workDir.DIRECTORY_SEPARATOR.'job';

        $this->fs = new Filesystem();
    }

    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getArchivePath(): string
    {
        return $this->workDir;
    }

    public function getJobPropertiesList(): array
    {
        return $this->jobPropertiesList;
    }

    public function getTaskPropertiesList(): array
    {
        return $this->taskPropertiesList;
    }

    protected function getInflector(): Inflector
    {
        return $this->inflector;
    }

    public function getTmpEntityPath(RunnableEntityInterface $entity): string
    {
        if ($entity instanceof JobEntityInterface) {
            return $this->getBasePath()
                .DIRECTORY_SEPARATOR
                .$entity->getId();
        } elseif ($entity instanceof TaskEntityInterface) {
            return $this->getBasePath()
                .DIRECTORY_SEPARATOR
                .$entity->getJob()->getId()
                .DIRECTORY_SEPARATOR
                .'tasks'
                .DIRECTORY_SEPARATOR
                .$entity->getNum();
        }
    }

    public function getArchiveEntityPath(RunnableEntityInterface $entity): string
    {
        if ($entity instanceof JobEntityInterface) {
            return $this->getArchivePath()
                .DIRECTORY_SEPARATOR
                .$entity->getId();
        } elseif ($entity instanceof TaskEntityInterface) {
            return $this->getArchivePath()
                .DIRECTORY_SEPARATOR
                .$entity->getJob()->getId()
                .DIRECTORY_SEPARATOR
                .'tasks'
                .DIRECTORY_SEPARATOR
                .$entity->getNum();
        }
    }

    public function archive(JobEntityInterface $job): void
    {
        $tmp = $this->getEntityPath($job);
        $archive = $this->getArchiveEntityPath($job);
        $this->fs->rename($tmp, $archive);
    }

    /**
     * @param RunnableEntityInterface $entity
     * @param bool $archive
     * @return string
     */
    public function getEntityPath(RunnableEntityInterface $entity, bool $archive = false): string
    {
        $path = $this->getTmpEntityPath($entity);
/*        if ($entity instanceof JobEntityInterface) {
            $path = $this->getBasePath()
                .DIRECTORY_SEPARATOR
                .$entity->getId();
        } elseif ($entity instanceof TaskEntityInterface) {
            $path = $this->getBasePath()
                .DIRECTORY_SEPARATOR
                .$entity->getJob()->getId()
                .DIRECTORY_SEPARATOR
                .'tasks'
                .DIRECTORY_SEPARATOR
                .$entity->getNum();
        }*/
        if (!file_exists($path) && $archive) {
            return $this->getArchiveEntityPath($entity);
        } else {
            return $path;
        }
    }

    /**
     * @param RunnableEntityInterface $entity
     * @param string                  $property
     */
    public function persist(RunnableEntityInterface $entity, string $property = ''): void
    {
        if ($entity instanceof JobEntityInterface) {
            $type = 'job';
            $props = $this->getJobPropertiesList();
            $tasks = $entity->getTasks();
        } else {
            $type = 'task';
            $props = $this->getTaskPropertiesList();
        }

        $path = $this->getEntityPath($entity);

        /*$fs = new Filesystem();*/

        if (!$property) {
            if (!\file_exists($path)) {
                $this->fs->mkdir($path, 0700);
            }
            // Persist all properties
            foreach ($props as $prop) {
                $this->persist($entity, $prop);
            }
            // Persist all tasks
            if (isset($tasks)) {
                foreach ($tasks as $task) {
                    $this->persist($task);
                }
            }
        } else {
            if (!\file_exists($path)) {
                $id = 'job' == $type
                    ? $entity->getId()
                    : $entity->getJob()->getId().'/'.$entity->getNum();
                throw new \LogicException("[$id] $type not found. You must persist it before single property");
            }

            // Persist the given property
            $property = strtolower($property);
            if (!\in_array($property, $props)) {
                throw new \InvalidArgumentException("\"$property\" is not a valid $type property)");
            }
            $path .= DIRECTORY_SEPARATOR.$property;
            $method = 'get'.$this->getInflector()->classify($property);
            $this->fs->dumpFile($path, $entity->$method());
        }
    }

    public function refresh(RunnableEntityInterface $entity, string $property = ''): void
    {
        if ($entity instanceof JobEntityInterface) {
            $type = 'job';
            $props = $this->getJobPropertiesList();
        } else {
            $type = 'task';
            $props = $this->getTaskPropertiesList();
        }

        $path = $this->getEntityPath($entity, true);

        if (!$property) {
            // Refresh all properties
            foreach ($props as $prop) {
                $this->refresh($entity, $prop);
            }
        } else {
            if (!\file_exists($path)) {
                if ('job' === $type) {
                    $ids = [
                        $entity->getId(),
                    ];
                } else {
                    $ids = [
                        $entity->getJob()->getId(),
                        $entity->getNum(),
                    ];
                }
                throw new JobManagerEntityNotFoundException($ids);
            }

            // Refresh the given property
            $property = strtolower($property);
            if (!\in_array($property, $props)) {
                throw new \InvalidArgumentException("\"$property\" is not a valid $type property)");
            }
            $path .= DIRECTORY_SEPARATOR.$property;
            $value = \file_get_contents($path);
            $method = 'set'.$this->getInflector()->classify($property);
            $entity->$method($value);
        }
    }

    /**
     * @param string $class
     * @param string $jobId
     * @param int    $taskNum
     *
     * @return RunnableEntityInterface
     *
     * @throws JobManagerEntityNotFoundException
     */
    public function find(string $class, string $jobId, int $taskNum = -1): RunnableEntityInterface
    {
        if (\in_array($class, ['job', 'task'])) {
            $class = $this->getEntityClass($class);
        }

        $interfaces = \class_implements($class);

        if (\in_array(JobEntityInterface::class, $interfaces)) {
            $entity = new $class($jobId);
        } elseif (\in_array(TaskEntityInterface::class, $interfaces)) {
            $entity = new $class($jobId, $taskNum);
        } else {
            throw new \InvalidArgumentException("Invalid entity class \"$class\"");
        }
        if ($jobId) {
            $this->refresh($entity);
        } else {
            $this->persist($entity);
        }
        return $entity;
    }

    public function getEntityClass(string $type): string
    {
        if ($type === 'job') {
            return JobEntity::class;
        } else if ($type === 'class') {
            return TaskEntity::class;
        }
        throw new \InvalidArgumentException("Invalid entity type \"$type\" valid values are \"job\" and \"task\"");
    }
}
