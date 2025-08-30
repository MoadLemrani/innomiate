<?php

namespace App\Entity;

use App\Enum\InvitationStatus;
use App\Repository\InvitationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'sentInvitations')]
    #[ORM\JoinColumn(name: 'sender_participant_id', nullable: false)]
    private ?Participant $senderParticipant = null;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'receivedInvitations')]
    #[ORM\JoinColumn(name: 'receiver_participant_id', nullable: false)]
    private ?Participant $receiverParticipant = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'invitations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Team $team = null;

    #[ORM\Column(type: 'string', enumType: InvitationStatus::class)]
    private InvitationStatus $status = InvitationStatus::PENDING;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Sets current time when the object is created
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSenderParticipant(): ?Participant
    {
        return $this->senderParticipant;
    }
    public function setSenderParticipant(?Participant $senderParticipant): self
    {
        $this->senderParticipant = $senderParticipant;
        return $this;
    }

    public function getReceiverParticipant(): ?Participant
    {
        return $this->receiverParticipant;
    }
    public function setReceiverParticipant(?Participant $receiverParticipant): self
    {
        $this->receiverParticipant = $receiverParticipant;
        return $this;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }
    public function setTeam(?Team $team): self
    {
        $this->team = $team;
        return $this;
    }

    public function getStatus(): InvitationStatus
    {
        return $this->status;
    }
    public function setStatus(InvitationStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
