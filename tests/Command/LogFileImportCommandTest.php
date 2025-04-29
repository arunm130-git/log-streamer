<?php

namespace App\Tests\Command;

use App\Command\LogFileImportCommand;
use App\Service\LogFileImporterService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Command\Command;

class LogFileImportCommandTest extends TestCase
{
    private LogFileImporterService $logFileImporter;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->logFileImporter = $this->createMock(LogFileImporterService::class);
        $command = new LogFileImportCommand($this->logFileImporter);
        $this->commandTester = new CommandTester($command);
    }

    /**
     * Tests successful log file import.
     *
     * Asserts that the command executes successfully and the correct number
     * of log entries is reported.
     */
    public function testCommandImportsLogFileSuccessfully(): void
    {
        $filePath = '/path/to/logfile.log';

        $this->logFileImporter
            ->expects($this->once())
            ->method('importLogs')
            ->with($filePath)
            ->willReturn(10);

        $this->commandTester->execute([
            'file' => $filePath
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::SUCCESS, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('10 log entries processed and imported successfully!', $output);
    }

    /**
     * Tests that a RuntimeException during import results in command failure.
     *
     * Asserts that the error message is shown and command exits with failure code.
     */
    public function testCommandHandlesRuntimeException(): void
    {
        $filePath = '/path/to/invalidfile.log';

        $this->logFileImporter
            ->expects($this->once())
            ->method('importLogs')
            ->with($filePath)
            ->willThrowException(new \RuntimeException('File not found'));

        $this->commandTester->execute([
            'file' => $filePath
        ]);

        $output = $this->commandTester->getDisplay();

        $this->assertEquals(Command::FAILURE, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('File not found', $output);
    }
}
