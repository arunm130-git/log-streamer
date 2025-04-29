<?php

namespace App\Tests\Service;

use App\Entity\LogEntry;
use App\Service\LogFileImporterService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class LogFileImporterServiceTest extends TestCase
{
    private MockObject $entityManager;
    private string $testLogFile;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a mock for the EntityManagerInterface
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Dynamically create a temporary log file for testing
        $this->testLogFile = sys_get_temp_dir() . '/test_logfile.log';
        $this->generateTestLogFile(150); // Create 150 log entries for testing
    }

    protected function tearDown(): void
    {
        // Clean up the generated log file after the test
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }

        parent::tearDown();
    }

    private function generateTestLogFile(int $entryCount): void
    {
        // Open the file in write mode
        $file = fopen($this->testLogFile, 'w');

        // Generate entries dynamically
        for ($i = 0; $i < $entryCount; $i++) {
            $logEntry = sprintf(
                "service%d - - [12/May/2021:10:00:00 +0000] \"GET /path%d HTTP/1.1\" 200\n",
                rand(1, 10), // Random service name
                rand(1, 100)  // Random path
            );
            fwrite($file, $logEntry);
        }

        fclose($file);
    }

    public function testImportLogsSuccessfullyProcessesValidLogFile(): void
    {
        // Mock the flush method to track its calls
        $this->entityManager->expects($this->exactly(2)) // Expect 2 flush calls for 150 entries
        ->method('flush');

        // Create the service instance with the mocked EntityManager
        $logFileImporterService = new LogFileImporterService($this->entityManager);

        // Call the method to import logs
        $processedLines = $logFileImporterService->importLogs($this->testLogFile);

        // Assert the number of processed lines
        $this->assertEquals(150, $processedLines);  // Adjust based on the number of entries
    }
}
