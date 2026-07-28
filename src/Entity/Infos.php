<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InfosRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfosRepository::class)]
#[ORM\Table(name: '`infos`')]
class Infos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rank = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $victoire = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $defaite = null;

    #[ORM\OneToOne(inversedBy: 'infos', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRank(): ?string
    {
        return $this->rank;
    }

    public function setRank(?string $rank): static
    {
        $this->rank = $rank;

        return $this;
    }

    public function getVictoire(): ?string
    {
        return $this->victoire;
    }

    public function setVictoire(?string $victoire): static
    {
        $this->victoire = $victoire;

        return $this;
    }

    public function getDefaite(): ?string
    {
        return $this->defaite;
    }

    public function setDefaite(?string $defaite): static
    {
        $this->defaite = $defaite;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        if ($user !== null && $user->getInfos() !== $this) {
            $user->setInfos($this);
        }

        return $this;
    }
}
