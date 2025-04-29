<?php

namespace App\Service;

use App\DTO\LogCountFilterDTO;

interface LogAnalyticsServiceInterface
{
    public function getCount(LogCountFilterDTO $filter): int;

    public function truncateLogs(): void;
}
