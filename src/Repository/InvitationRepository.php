<?php

namespace App\Repository;

use App\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Enum\InvitationStatus;
use App\Entity\Participant;
use App\Entity\Team;

/**
 * @extends ServiceEntityRepository<Invitation>
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    public function findPendingForParticipant(Participant $participant): array
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.receiver_participant = :participant')
            ->andWhere('i.status = :status')
            ->setParameter('participant', $participant)
            ->setParameter('status', InvitationStatus::PENDING)
            ->getQuery()
            ->getResult();
    }

    public function findOneByParticipantsAndTeam(Participant $sender, Participant $receiver, Team $team): ?Invitation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.sender_participant = :sender')
            ->andWhere('i.receiver_participant = :receiver')
            ->andWhere('i.team = :team')
            ->setParameter('sender', $sender)
            ->setParameter('receiver', $receiver)
            ->setParameter('team', $team)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countReceivedInvitations(?Participant $participant): int
    {
        if ($participant === null) {
            return 0;
        }

        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->andWhere('i.receiver_participant = :participant')
            ->setParameter('participant', $participant)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
