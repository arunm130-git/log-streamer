<?php

namespace App\Service;

use App\Entity\LogEntry;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

/**
 * Service responsible for importing log entries from a file into the database.
 */
class LogFileImporterService implements LogFileImporterInterface
{
    private const BATCH_SIZE = 200;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FilePointerManagerInterface $filePointerManager
    ) {}

    /**
     * Imports log entries from the given file path.
     *
     * @param string $filePath Absolute or relative path to the log file.
     * @return int Number of lines successfully processed.
     *
     * @throws RuntimeException If the file doesn't exist, can't be opened, or contains invalid entries.
     */
    public function importLogs(string $filePath): int
    {
        $linesProcessed = 0;
        $maxLinesPerRun = 10000;

        // Retrieve the read file pointer (byte offset) of the log file
        $filePointer = $this->filePointerManager->getFilePointer($filePath);

        if (!file_exists($filePath)) {
            throw new RuntimeException("The log file '$filePath' does not exist.");
        }

        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new RuntimeException("Unable to open the log file '$filePath'.");
        }

        // Move the file pointer to the saved position using fseek
        if ($filePointer > 0) {
            fseek($handle, $filePointer);
        }

        // Parse each line
        while (($line = fgets($handle)) !== false) {
            $this->processAndSaveLogEntry($line);
            $linesProcessed++;

            if ($linesProcessed % self::BATCH_SIZE === 0) {
                try {
                    $this->entityManager->beginTransaction();

                    // Flush the current batch to the database
                    $this->entityManager->flush();
                    $this->entityManager->clear();

                    // After each batch, update the file pointer in storage
                    $currentPointer = ftell($handle);

                    if ($currentPointer === false) {
                        throw new RuntimeException("Unable to get the current file pointer position.");
                    }
                    $this->filePointerManager->setFilePointer($filePath, $currentPointer);

                    // Commit the transaction after the batch is processed
                    $this->entityManager->commit();
                } catch (\Exception $e) {

                    // If an error occurs, rollback the transaction
                    $this->entityManager->rollback();
                    throw new RuntimeException("An error occurred while processing the batch: " . $e->getMessage());
                }
            }

            // Stop processing after the max allowed lines to ensure short runs
            if ($linesProcessed >= $maxLinesPerRun) {
                break;
            }
        }

        fclose($handle);

        $this->entityManager->flush();
        $this->entityManager->clear();

        return $linesProcessed;
    }

    /**
     * Parses a log line and saves it as a LogEntry entity if valid.
     *
     * @param string $logEntry Raw log line.
     *
     * @throws RuntimeException If the log format or timestamp is invalid.
     */
    private function processAndSaveLogEntry(string $logEntry): void
    {
        $logEntry = trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $logEntry));

        $pattern = '/^([A-Z0-9\-]+) - - \[([^\]]+)\] "([A-Z]+) ([^ ]+) HTTP\/[\d.]+" (\d{3})$/';

        if (preg_match($pattern, $logEntry, $matches)) {
            $serviceName = $matches[1];
            $timestampString = $matches[2];
            $httpMethod = $matches[3];
            $path = $matches[4];
            $statusCode = (int) $matches[5];

            $timestamp = \DateTimeImmutable::createFromFormat('d/M/Y:H:i:s O', $timestampString);

            if (!$timestamp) {
                throw new RuntimeException("Invalid timestamp format: " . $timestampString);
            }

            $log = new LogEntry();
            $log->setServiceName($serviceName);
            $log->setTimestamp($timestamp);
            $log->setHttpMethod($httpMethod);
            $log->setPath($path);
            $log->setStatusCode($statusCode);

            $this->entityManager->persist($log);
        } else {
            throw new RuntimeException("Failed to parse log entry: " . $logEntry);
        }
    }
}
