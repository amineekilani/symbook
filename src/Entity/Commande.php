<?php

namespace App\Entity;

use App\Enum\TStatutCommande;
use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity(repositoryClass: CommandeRepository::class)]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $commandeDetails = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripeSessionId = null;

    #[ORM\Column(type: 'string', enumType: TStatutCommande::class)]
    private TStatutCommande $statut;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->statut = TStatutCommande::EN_ATTENTE;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): self
    {
        $this->total = $total;

        return $this;
    }

    public function getCommandeDetails(): ?string
    {
        return $this->commandeDetails;
    }

    public function setCommandeDetails(?string $commandeDetails): self
    {
        $this->commandeDetails = $commandeDetails;

        return $this;
    }

    public function getCommandeDetailsArray(): ?array
    {
        return $this->commandeDetails ? json_decode($this->commandeDetails, true) : null;
    }

    public function getStripeSessionId(): ?string
    {
        return $this->stripeSessionId;
    }

    public function setStripeSessionId(?string $stripeSessionId): self
    {
        $this->stripeSessionId = $stripeSessionId;

        return $this;
    }

    public function getStatut(): ?TStatutCommande
    {
        return $this->statut;
    }

    public function setStatut(TStatutCommande $statut): self
    {
        $this->statut = $statut;

        return $this;
    }
}