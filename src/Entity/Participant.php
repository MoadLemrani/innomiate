<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Relationships
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Competition::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Competition $competition = null;

    #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'members')]
    private ?Team $team = null;

    // Team-related fields
    #[ORM\Column]
    private bool $isTeamLeader = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $joinedTeamDate = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    // Participant details from second version
    #[ORM\Column(length: 255)]
    private ?string $participantCode = null;

    #[ORM\Column(length: 50)]
    private ?string $nom = null;

    #[ORM\Column(length: 70)]
    private ?string $prenom = null;

    #[ORM\Column(length: 150)]
    private ?string $courrierProfessionnel = null;

    #[ORM\Column(length: 20)]
    private ?string $pays = null;

    #[ORM\Column(length: 20)]
    private ?string $ville = null;

    #[ORM\Column(length: 255)]
    private ?string $CIN = null;

    #[ORM\Column(length: 255)]
    private ?string $profession = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $NiveauEtude = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $Etablissement = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $CarteAttestation = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $specialite = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $statut = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fonction = null;

    #[ORM\Column(length: 10)]
    private ?string $partage = null;

    // Collections for relationships with cascade delete
    #[ORM\OneToMany(mappedBy: 'senderParticipant', targetEntity: Invitation::class, cascade: ['remove'])]
    private Collection $sentInvitations;

    #[ORM\OneToMany(mappedBy: 'receiverParticipant', targetEntity: Invitation::class, cascade: ['remove'])]
    private Collection $receivedInvitations;

    #[ORM\OneToMany(mappedBy: 'participant', targetEntity: Pitch::class, cascade: ['remove'])]
    private Collection $pitches;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable(); // Sets current time when the object is created
        $this->sentInvitations = new ArrayCollection();
        $this->receivedInvitations = new ArrayCollection();
        $this->pitches = new ArrayCollection();
    }

    // ID getter
    public function getId(): ?int
    {
        return $this->id;
    }

    // Relationship getters/setters
    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function getCompetition(): ?Competition { return $this->competition; }
    public function setCompetition(?Competition $competition): self { $this->competition = $competition; return $this; }

    public function getTeam(): ?Team { return $this->team; }
    public function setTeam(?Team $team): self { $this->team = $team; return $this; }

    public function isTeamLeader(): bool { return $this->isTeamLeader; }
    public function setIsTeamLeader(bool $isTeamLeader): self { $this->isTeamLeader = $isTeamLeader; return $this; }

    public function getJoinedTeamDate(): ?\DateTimeInterface { return $this->joinedTeamDate; }
    public function setJoinedTeamDate(?\DateTimeInterface $joinedTeamDate): self { $this->joinedTeamDate = $joinedTeamDate; return $this; }

    public function getCreatedAt(): ?\DateTimeInterface { return $this->createdAt; }
    public function setCreatedAt(\DateTimeInterface $createdAt): self { $this->createdAt = $createdAt; return $this; }

    // Participant details getters/setters
    public function getParticipantCode(): ?string { return $this->participantCode; }
    public function setParticipantCode(string $participantCode): self { $this->participantCode = $participantCode; return $this; }

    public function getNom(): ?string { return $this->nom; }
    public function setNom(string $nom): self { $this->nom = $nom; return $this; }

    public function getPrenom(): ?string { return $this->prenom; }
    public function setPrenom(string $prenom): self { $this->prenom = $prenom; return $this; }

    public function getCourrierProfessionnel(): ?string { return $this->courrierProfessionnel; }
    public function setCourrierProfessionnel(string $courrierProfessionnel): self { $this->courrierProfessionnel = $courrierProfessionnel; return $this; }

    public function getPays(): ?string { return $this->pays; }
    public function setPays(string $pays): self { $this->pays = $pays; return $this; }

    public function getVille(): ?string { return $this->ville; }
    public function setVille(string $ville): self { $this->ville = $ville; return $this; }

    public function getCIN(): ?string { return $this->CIN; }
    public function setCIN(string $CIN): self { $this->CIN = $CIN; return $this; }

    public function getProfession(): ?string { return $this->profession; }
    public function setProfession(string $profession): self { $this->profession = $profession; return $this; }

    public function getNiveauEtude(): ?string { return $this->NiveauEtude; }
    public function setNiveauEtude(?string $NiveauEtude): self { $this->NiveauEtude = $NiveauEtude; return $this; }

    public function getEtablissement(): ?string { return $this->Etablissement; }
    public function setEtablissement(?string $Etablissement): self { $this->Etablissement = $Etablissement; return $this; }

    public function getCarteAttestation(): ?string { return $this->CarteAttestation; }
    public function setCarteAttestation(?string $CarteAttestation): self { $this->CarteAttestation = $CarteAttestation; return $this; }

    public function getSpecialite(): ?string { return $this->specialite; }
    public function setSpecialite(?string $specialite): self { $this->specialite = $specialite; return $this; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): self { $this->statut = $statut; return $this; }

    public function getFonction(): ?string { return $this->fonction; }
    public function setFonction(?string $fonction): self { $this->fonction = $fonction; return $this; }

    public function getPartage(): ?string { return $this->partage; }
    public function setPartage(string $partage): self { $this->partage = $partage; return $this; }

    // Collection methods
    /**
     * @return Collection<int, Invitation>
     */
    public function getSentInvitations(): Collection { return $this->sentInvitations; }
    public function addSentInvitation(Invitation $sentInvitation): self {
        if (!$this->sentInvitations->contains($sentInvitation)) {
            $this->sentInvitations->add($sentInvitation);
            $sentInvitation->setSenderParticipant($this);
        }
        return $this;
    }
    public function removeSentInvitation(Invitation $sentInvitation): self {
        if ($this->sentInvitations->removeElement($sentInvitation)) {
            if ($sentInvitation->getSenderParticipant() === $this) {
                $sentInvitation->setSenderParticipant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getReceivedInvitations(): Collection { return $this->receivedInvitations; }
    public function addReceivedInvitation(Invitation $receivedInvitation): self {
        if (!$this->receivedInvitations->contains($receivedInvitation)) {
            $this->receivedInvitations->add($receivedInvitation);
            $receivedInvitation->setReceiverParticipant($this);
        }
        return $this;
    }
    public function removeReceivedInvitation(Invitation $receivedInvitation): self {
        if ($this->receivedInvitations->removeElement($receivedInvitation)) {
            if ($receivedInvitation->getReceiverParticipant() === $this) {
                $receivedInvitation->setReceiverParticipant(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Pitch>
     */
    public function getPitches(): Collection { return $this->pitches; }
    public function addPitch(Pitch $pitch): self {
        if (!$this->pitches->contains($pitch)) {
            $this->pitches->add($pitch);
            $pitch->setParticipant($this);
        }
        return $this;
    }
    public function removePitch(Pitch $pitch): self {
        if ($this->pitches->removeElement($pitch)) {
            if ($pitch->getParticipant() === $this) {
                $pitch->setParticipant(null);
            }
        }
        return $this;
    }
}