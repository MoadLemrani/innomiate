<?php

namespace App\Entity;

use App\Repository\PitchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PitchRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_PARTICIPANT_COMPETITION', fields: ['participant', 'competition'])]
class Pitch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'pitches')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $participant = null;

    #[ORM\ManyToOne(targetEntity: Competition::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competition $competition = null;

    #[ORM\Column(type: 'text')]
    private ?string $content = null;

    #[ORM\Column(type: 'text')]
    private ?string $contactInfo = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }
    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;
        return $this;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }
    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getContactInfo(): ?string
    {
        return $this->contactInfo;
    }
    public function setContactInfo(string $contactInfo): self
    {
        $this->contactInfo = $contactInfo;
        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Sets current time when the object is created
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
