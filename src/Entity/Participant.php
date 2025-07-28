<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
class Participant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

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
    private ?string $NiveauEtude  = null;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCourrierProfessionnel(): ?string
    {
        return $this->courrierProfessionnel;
    }

    public function setCourrierProfessionnel(string $courrierProfessionnel): static
    {
        $this->courrierProfessionnel = $courrierProfessionnel;

        return $this;
    }

    public function getPays(): ?string
    {
        return $this->pays;
    }

    public function setPays(string $pays): static
    {
        $this->pays = $pays;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;

        return $this;
    }

    public function getCIN(): ?string
    {
        return $this->CIN;
    }

    public function setCIN(string $CIN): static
    {
        $this->CIN = $CIN;

        return $this;
    }

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(string $profession): static
    {
        $this->profession = $profession;

        return $this;
    }

    public function getNiveauEtude(): ?string
    {
        return $this->NiveauEtude;
    }

    public function setNiveauEtude(?string $NiveauEtude): static
    {
        $this->NiveauEtude = $NiveauEtude;

        return $this;
    }

    public function getEtablissement(): ?string
    {
        return $this->Etablissement;
    }

    public function setEtablissement(?string $Etablissement): static
    {
        $this->Etablissement = $Etablissement;

        return $this;
    }

    public function getCarteAttestation(): ?string
    {
        return $this->CarteAttestation;
    }

    public function setCarteAttestation(?string $CarteAttestation): static
    {
        $this->CarteAttestation = $CarteAttestation;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(?string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(?string $fonction): static
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getPartage(): ?string
    {
        return $this->partage;
    }

    public function setPartage(string $partage): static
    {
        $this->partage = $partage;

        return $this;
    }
}
