<?php

namespace App\Tests\Service;

use App\Entity\LogEntry;
use App\Service\LogFileImporterService;
use App\Service\FilePointerManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LogFileImporterServiceTest extends TestCase
{
    private MockObject $entityManager;
    private MockObject $filePointerManager;
    private string $testLogFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->filePointerManager = $this->createMock(FilePointerManagerInterface::class);

        $this->testLogFile = sys_get_temp_dir() . '/test_logfile.log';
        $this->generateTestLogFile(150);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }

        parent::tearDown();
    }

    private function generateTestLogFile(int $entryCount): void
    {
        $file = fopen($this->testLogFile, 'w');

        for ($i = 0; $i < $entryCount; $i++) {
            $logEntry = sprintf(
                "SERVICE-%d - - [12/May/2021:10:00:00 +0000] \"GET /path%d HTTP/1.1\" 200\n",
                rand(1, 10),
                rand(1, 100)
            );
            fwrite($file, $logEntry);
        }

        fclose($file);
    }

    public function testImportLogsSuccessfullyProcessesValidLogFile(): void
    {
        // Create service with mocks
        $logFileImporterService = new LogFileImporterService(
            $this->entityManager,
            $this->filePointerManager
        );

        // Act
        $processedLines = $logFileImporterService->importLogs($this->testLogFile);

        // Assert
        $this->assertEquals(150, $processedLines);
    }
}
