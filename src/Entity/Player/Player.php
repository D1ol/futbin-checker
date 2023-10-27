<?php

namespace App\Entity\Player;

use App\Repository\Player\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $cardId = null;

    #[ORM\Column]
    private ?int $baseId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): Player
    {
        $this->name = $name;

        return $this;
    }

    public function getCardId(): ?int
    {
        return $this->cardId;
    }

    public function setCardId(?int $cardId): Player
    {
        $this->cardId = $cardId;

        return $this;
    }

    public function getBaseId(): ?int
    {
        return $this->baseId;
    }

    public function setBaseId(?int $baseId): Player
    {
        $this->baseId = $baseId;

        return $this;
    }
}
