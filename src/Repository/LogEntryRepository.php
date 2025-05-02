<?php

namespace App\Repository;

use App\DTO\LogCountFilterDTO;
use App\Entity\LogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LogEntry>
 */
class LogEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LogEntry::class);
    }

    public function countFiltered(LogCountFilterDTO $filter): int
    {
        $qb = $this->createQueryBuilder('log')
            ->select('COUNT(log.id)');

        if ($filter->serviceNames) {
            $qb->andWhere('log.serviceName IN (:services)')
                ->setParameter('services', $filter->serviceNames);
        }

        if ($filter->startDate) {
            $qb->andWhere('log.timestamp >= :start')
                ->setParameter('start', $filter->startDate);
        }

        if ($filter->endDate) {
            $qb->andWhere('log.timestamp <= :end')
                ->setParameter('end', $filter->endDate);
        }

        if ($filter->statusCode !== null) {
            $qb->andWhere('log.statusCode IN (:status)')
                ->setParameter('status', $filter->statusCode);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function truncate(): void
    {
        $connection = $this->getEntityManager()->getConnection();
        $platform = $connection->getDatabasePlatform();

        $connection->executeStatement(
            $platform->getTruncateTableSQL($this->getClassMetadata()->getTableName(), true)
        );
    }
}
