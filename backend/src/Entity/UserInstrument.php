<?php

namespace App\Entity;

use App\Repository\UserInstrumentRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Niveau;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserInstrumentRepository::class)]
#[ApiResource]
class UserInstrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userInstruments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read'])]
    private ?Instrument $instrument = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isMain = null;

    #[ORM\Column(type: 'string', enumType: Niveau::class)]
    #[Groups(['user:read'])]
    private ?Niveau $niveau = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getInstrument(): ?Instrument
    {
        return $this->instrument;
    }

    public function setInstrument(?Instrument $instrument): static
    {
        $this->instrument = $instrument;

        return $this;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    #[Groups(['user:read'])]
    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function setIsMain(bool $isMain): static
    {
        $this->isMain = $isMain;

        return $this;
    }

    public function getNiveau(): ?Niveau
    {
        return $this->niveau;
    }

    public function setNiveau(?Niveau $niveau): static
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function __toString(): string
    {
        $str = $this->instrument ? $this->instrument->getNomInstrument() : 'Instrument';
        if ($this->niveau) $str .= ' (' . $this->niveau->value . ')';
        if ($this->isMain) $str .= ' - Principal';
        return $str;
    }
}
