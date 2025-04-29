<?php

namespace App\Service;

use App\DTO\LogCountFilterDTO;
use App\Repository\LogEntryRepository;

class LogAnalyticsService implements LogAnalyticsServiceInterface
{
    public function __construct(private readonly LogEntryRepository $repository) {}

    public function getCount(LogCountFilterDTO $filter): int
    {
        return $this->repository->countFiltered($filter);
    }

    public function truncateLogs(): void
    {
        $this->repository->truncate();
    }
}
