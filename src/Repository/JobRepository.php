<?php

namespace Bnza\JobManagerBundle\Repository;

class JobRepository
{
    /**
     * @var ActiveTaskRepositoryInterface
     */
    private $activeTaskRepo;

    /**
     * @var TaskRepositoryInterface
     */
    private $archivedTaskRepo;

    /**
     * @param TaskRepositoryInteface $activeTaskRepo
     * @param TaskRepository $archivedTaskRepo
     */
    public function __construct(ActiveTaskRepositoryInterface $activeTaskRepo, TaskRepositoryInterface $archivedTaskRepo)
    {
        $this->activeTaskRepo = $activeTaskRepo;
        $this->archivedTaskRepo = $archivedTaskRepo;
    }

    public function exists(string $uuid): bool
    {
        return  $this->activeTaskRepo->exists($uuid)
            || $this->archivedTaskRepo->exists($uuid);
    }

    /**
     *
     * @return bool Whether the given task uuid is locked or exists in some repository
     */
    public function existsUuid(string $uuid): bool
    {
        return $this->activeTaskRepo->isLocked($uuid)
            || $this->exists($uuid);
    }

    /**
     * @return bool
     */
    public function lock(string $uuid): bool
    {
        return $this->activeTaskRepo->lock($uuid);
    }
}
