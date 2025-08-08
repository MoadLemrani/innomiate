<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $minTeamSize = null;

    #[ORM\Column]
    private ?int $maxTeamSize = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $imagePath = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $moreInfoLink = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;


    #[ORM\OneToMany(mappedBy: 'competition', targetEntity: Team::class)]
    private Collection $teams;

    #[ORM\OneToMany(mappedBy: 'competition', targetEntity: Participant::class)]
    private Collection $participants;

    public function __construct()
    {
        $this->teams = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

    // Getters and Setters
    public function getId(): ?int
    {
        return $this->id;
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

    public function getMinTeamSize(): ?int
    {
        return $this->minTeamSize;
    }
    public function setMinTeamSize(int $minTeamSize): self
    {
        $this->minTeamSize = $minTeamSize;
        return $this;
    }

    public function getMaxTeamSize(): ?int
    {
        return $this->maxTeamSize;
    }
    public function setMaxTeamSize(int $maxTeamSize): self
    {
        $this->maxTeamSize = $maxTeamSize;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }
    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }
    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function setImagePath(?string $imagePath): self
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getMoreInfoLink(): ?string
    {
        return $this->moreInfoLink;
    }

    public function setMoreInfoLink(?string $moreInfoLink): self
    {
        $this->moreInfoLink = $moreInfoLink;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return Collection<int, Team>
     */
    public function getTeams(): Collection
    {
        return $this->teams;
    }
    public function addTeam(Team $team): self
    {
        if (!$this->teams->contains($team)) {
            $this->teams->add($team);
            $team->setCompetition($this);
        }
        return $this;
    }
    public function removeTeam(Team $team): self
    {
        if ($this->teams->removeElement($team)) {
            if ($team->getCompetition() === $this) {
                $team->setCompetition(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }
    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setCompetition($this);
        }
        return $this;
    }
    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            if ($participant->getCompetition() === $this) {
                $participant->setCompetition(null);
            }
        }
        return $this;
    }
}
