<?php

namespace App\Tests\Service;

use App\DTO\LogCountFilterDTO;
use App\Repository\LogEntryRepository;
use App\Service\LogAnalyticsService;
use PHPUnit\Framework\TestCase;

class LogAnalyticsServiceTest extends TestCase
{
    public function testGetCountReturnsExpectedValue(): void
    {
        $mockFilter = $this->createMock(LogCountFilterDTO::class);

        $repository = $this->createMock(LogEntryRepository::class);
        $repository->expects($this->once())
            ->method('countFiltered')
            ->with($mockFilter)
            ->willReturn(42);

        $service = new LogAnalyticsService($repository);

        $result = $service->getCount($mockFilter);

        $this->assertSame(42, $result);
    }

    public function testTruncateLogsCallsRepositoryTruncate(): void
    {
        $repository = $this->createMock(LogEntryRepository::class);
        $repository->expects($this->once())
            ->method('truncate');

        $service = new LogAnalyticsService($repository);

        $service->truncateLogs();
    }
}
