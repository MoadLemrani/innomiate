<?php

namespace App\Repository;

use App\Entity\Pitch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Participant;

/**
 * @extends ServiceEntityRepository<Pitch>
 */
class PitchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pitch::class);
    }

    public function findOneByParticipant(Participant $participant): ?Pitch
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.participant = :participant')
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Update a Pitch
     */
    /*public function update(Pitch $pitch, bool $flush = true): void
    {
        // Entity is already tracked if fetched via Doctrine, so persist is optional.
        $this->_em->persist($pitch);
        if ($flush) {
            $this->_em->flush();
        }
    }*/

    /**
     * Remove a Pitch
     */
    /*public function remove(Pitch $pitch, bool $flush = true): void
    {
        $this->_em->remove($pitch);
        if ($flush) {
            $this->_em->flush();
        }
    }*/
}
