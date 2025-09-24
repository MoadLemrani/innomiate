<?php

namespace App\Entity;

use App\Repository\TeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $teamCode = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Competition::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competition $competition = null;

    #[ORM\ManyToOne(targetEntity: Participant::class)]
    #[ORM\JoinColumn(name: 'leader_participant_id', referencedColumnName: 'id', onDelete: 'SET NULL', nullable: true)]
    private ?Participant $leaderParticipant = null;

    // FIXED: Use datetime_immutable type for DateTimeImmutable objects
    #[ORM\Column(type: 'datetime_immutable')]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Participant::class)]
    private Collection $members;

    #[ORM\OneToMany(mappedBy: 'team', targetEntity: Invitation::class)]
    private Collection $invitations;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Sets current time when the object is created
        $this->members = new ArrayCollection();
        $this->invitations = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int 
    { 
        return $this->id; 
    }

    // FIXED: Proper method names with capital T
    public function getTeamCode(): ?string 
    { 
        return $this->teamCode; 
    }
    
    public function setTeamCode(string $teamCode): self 
    { 
        $this->teamCode = $teamCode; 
        return $this; 
    }

    public function getName(): ?string 
    { 
        return $this->name; 
    }
    
    public function setName(string $name): self 
    { 
        $this->name = $name; 
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

    public function getLeaderParticipant(): ?Participant 
    { 
        return $this->leaderParticipant; 
    }
    
    public function setLeaderParticipant(?Participant $leaderParticipant): self 
    { 
        $this->leaderParticipant = $leaderParticipant; 
        return $this; 
    }

    // FIXED: Return type should be DateTimeImmutable
    public function getCreatedAt(): ?\DateTimeImmutable 
    { 
        return $this->createdAt; 
    }
    
    public function setCreatedAt(\DateTimeImmutable $createdAt): self 
    { 
        $this->createdAt = $createdAt; 
        return $this; 
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getMembers(): Collection 
    { 
        return $this->members; 
    }
    
    public function addMember(Participant $member): self 
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setTeam($this);
        }
        return $this;
    }
    
    public function removeMember(Participant $member): self 
    {
        if ($this->members->removeElement($member)) {
            if ($member->getTeam() === $this) {
                $member->setTeam(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitations(): Collection 
    { 
        return $this->invitations; 
    }
    
    public function addInvitation(Invitation $invitation): self 
    {
        if (!$this->invitations->contains($invitation)) {
            $this->invitations->add($invitation);
            $invitation->setTeam($this);
        }
        return $this;
    }
    
    public function removeInvitation(Invitation $invitation): self 
    {
        if ($this->invitations->removeElement($invitation)) {
            if ($invitation->getTeam() === $this) {
                $invitation->setTeam(null);
            }
        }
        return $this;
    }

    /**
     * Auto-assign a new leader from existing members
     */
    public function autoAssignLeader(): void
    {
        if ($this->leaderParticipant === null && !$this->members->isEmpty()) {
            // Get the first member as new leader
            $newLeader = $this->members->first();
            if ($newLeader instanceof Participant) {
                $this->setLeaderParticipant($newLeader);
                $newLeader->setIsTeamLeader(true);
            }
        }
    }

    /**
     * Check if team has a leader
     */
    public function hasLeader(): bool
    {
        return $this->leaderParticipant !== null;
    }
}