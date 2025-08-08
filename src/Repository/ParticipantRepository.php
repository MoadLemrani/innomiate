<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Team;

/**
 * @extends ServiceEntityRepository<Participant>
 *
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    /**
     * Save a participant entity
     */
    public function save(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove a participant entity
     */
    public function remove(Participant $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Find participants by email (courrier professionnel)
     */
    public function findByEmail(string $email): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.courrierProfessionnel = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find participant by participant code
     */
    public function findByParticipantCode(string $participantCode): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.participantCode = :participantCode')
            ->setParameter('participantCode', $participantCode)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find participants by country
     */
    public function findByCountry(string $pays): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.pays = :pays')
            ->setParameter('pays', $pays)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find participants by profession
     */
    public function findByProfession(string $profession): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.profession = :profession')
            ->setParameter('profession', $profession)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find participants by city
     */
    public function findByCity(string $ville): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.ville = :ville')
            ->setParameter('ville', $ville)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find participants by speciality
     */
    public function findBySpecialite(string $specialite): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.specialite = :specialite')
            ->setParameter('specialite', $specialite)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find participants by status
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search participants by name (nom or prenom)
     */
    public function searchByName(string $searchTerm): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nom LIKE :searchTerm OR p.prenom LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find participants with shared data (partage = 'oui')
     */
    public function findWithSharedData(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.partage = :partage')
            ->setParameter('partage', 'oui')
            ->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Generate and assign unique participant codes
     */
    public function generateParticipantCode(Participant $participant, string $prefix = '#mia'): string
    {
        $em = $this->getEntityManager();

        do {
            // Generate a unique code with prefix + random string
            $code = $prefix . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

            // Check if code already exists
            $existing = $this->findByParticipantCode($code);
        } while ($existing !== null);

        return $code;
    }

    /**
     * Count participants by country
     */
    public function countByCountry(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.pays, COUNT(p.id) as count')
            ->groupBy('p.pays')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count participants by profession
     */
    public function countByProfession(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.profession, COUNT(p.id) as count')
            ->groupBy('p.profession')
            ->orderBy('count', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Advanced search with multiple criteria
     */
    public function advancedSearch(array $criteria): array
    {
        $qb = $this->createQueryBuilder('p');

        if (!empty($criteria['nom'])) {
            $qb->andWhere('p.nom LIKE :nom')
                ->setParameter('nom', '%' . $criteria['nom'] . '%');
        }

        if (!empty($criteria['prenom'])) {
            $qb->andWhere('p.prenom LIKE :prenom')
                ->setParameter('prenom', '%' . $criteria['prenom'] . '%');
        }

        if (!empty($criteria['pays'])) {
            $qb->andWhere('p.pays = :pays')
                ->setParameter('pays', $criteria['pays']);
        }

        if (!empty($criteria['ville'])) {
            $qb->andWhere('p.ville = :ville')
                ->setParameter('ville', $criteria['ville']);
        }

        if (!empty($criteria['profession'])) {
            $qb->andWhere('p.profession = :profession')
                ->setParameter('profession', $criteria['profession']);
        }

        if (!empty($criteria['specialite'])) {
            $qb->andWhere('p.specialite = :specialite')
                ->setParameter('specialite', $criteria['specialite']);
        }

        if (!empty($criteria['statut'])) {
            $qb->andWhere('p.statut = :statut')
                ->setParameter('statut', $criteria['statut']);
        }

        if (!empty($criteria['participantCode'])) {
            $qb->andWhere('p.participantCode = :participantCode')
                ->setParameter('participantCode', $criteria['participantCode']);
        }

        if (isset($criteria['hasParticipantCode'])) {
            if ($criteria['hasParticipantCode']) {
                $qb->andWhere('p.participantCode IS NOT NULL');
            } else {
                $qb->andWhere('p.participantCode IS NULL');
            }
        }

        return $qb->orderBy('p.nom', 'ASC')
            ->addOrderBy('p.prenom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser($user): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByCode(string $code): ?Participant
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.participant_code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countTeamMembers(Team $team): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.team = :team')
            ->setParameter('team', $team)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
