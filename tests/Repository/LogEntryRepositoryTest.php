<?php

namespace App\Tests\Repository;

use App\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use DateTimeImmutable;

class LogEntryRepositoryTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testSaveAndRetrieveLogEntry(): void
    {
        // Create new LogEntry
        $logEntry = new LogEntry();
        $logEntry->setServiceName('test-service');
        $logEntry->setTimestamp(new DateTimeImmutable('2025-04-29T15:00:00'));
        $logEntry->setHttpMethod('GET');
        $logEntry->setPath('/test-path');
        $logEntry->setStatusCode(200);

        // Persist
        $this->entityManager->persist($logEntry);
        $this->entityManager->flush();

        // Fetch from Repository
        $repository = $this->entityManager->getRepository(LogEntry::class);
        $savedLogEntry = $repository->find($logEntry->getId());

        // Assert
        $this->assertInstanceOf(LogEntry::class, $savedLogEntry);
        $this->assertEquals('test-service', $savedLogEntry->getServiceName());
        $this->assertEquals('GET', $savedLogEntry->getHttpMethod());
        $this->assertEquals('/test-path', $savedLogEntry->getPath());
        $this->assertEquals(200, $savedLogEntry->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null; // Avoid memory leaks
    }
}
