<?php

namespace App\Repository;

use App\Entity\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competition>
 */
class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    public function findActiveCompetitions(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.startDate <= :now')
            ->andWhere('c.endDate >= :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getResult();
    }
}
