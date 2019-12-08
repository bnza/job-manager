<?php

namespace Bnza\JobManagerBundle\Task;

use Bnza\JobManagerBundle\Entity\TaskEntityInterface;
use Bnza\JobManagerBundle\Event\TaskAttachedEvent;
use Bnza\JobManagerBundle\JobManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

abstract class AbstractTask implements TaskInterface
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var TaskEntityInterface
     */
    protected $entity;

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    final public function attach(JobManager $jobManager, ?string $uuid): void
    {
        $data = [
            'uuid' => $uuid,
            'class' => get_class($this),
            'description' => $this->getDescription()
        ];
        $this->entity = $jobManager->registerTaskEntity($data);
        foreach ($this->getSteps() as $task) {
            $task->attach($jobManager);
        }
        /**
         * @psalm-suppress TooManyArguments
         */
        $this->dispatcher->dispatch(new TaskAttachedEvent($this->entity), TaskEvents::ATTACHED);
    }
}
