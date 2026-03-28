<?php

namespace App\Entity;

use App\Repository\InfractionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfractionRepository::class)]
class Infraction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $typeInfraction = null;

    #[ORM\Column]
    private ?float $montantAmende = null;

    #[ORM\Column(length: 255)]
    private ?string $lieu = null;

    #[ORM\Column(length: 20)]
    private ?string $plaqueImmat = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateInfraction = null;

    #[ORM\Column(length: 50)]
    private ?string $statut = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\ManyToOne(inversedBy: 'infractions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'agentInfractions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $agent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeInfraction(): ?string
    {
        return $this->typeInfraction;
    }

    public function setTypeInfraction(string $typeInfraction): static
    {
        $this->typeInfraction = $typeInfraction;

        return $this;
    }

    public function getMontantAmende(): ?float
    {
        return $this->montantAmende;
    }

    public function setMontantAmende(float $montantAmende): static
    {
        $this->montantAmende = $montantAmende;

        return $this;
    }

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getPlaqueImmat(): ?string
    {
        return $this->plaqueImmat;
    }

    public function setPlaqueImmat(string $plaqueImmat): static
    {
        $this->plaqueImmat = $plaqueImmat;

        return $this;
    }

    public function getDateInfraction(): ?\DateTimeInterface
    {
        return $this->dateInfraction;
    }

    public function setDateInfraction(\DateTimeInterface $dateInfraction): static
    {
        $this->dateInfraction = $dateInfraction;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): static
    {
        $this->agent = $agent;

        return $this;
    }
}
