<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /**
     * Generate a unique team code like "#mia_team-XXXXXXXX"
     */
    public function generateUniqueTeamCode(): string
    {
        do {
            $code = '#mia_team-' . strtoupper(substr(md5(uniqid()), 0, 8));
            $existing = $this->findOneBy(['teamCode' => $code]);
        } while ($existing !== null);

        return $code;
    }

    //find by teamCode
    public function findByTeamCode(string $teamCode): ?Team
    {
        return $this->createQueryBuilder('t')
            ->where('t.teamCode = :code')
            ->setParameter('code', $teamCode)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
