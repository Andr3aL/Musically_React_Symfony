<?php

namespace App\Entity;

use App\Repository\UserStyleRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserStyleRepository::class)]
#[ApiResource]
class UserStyle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userStyles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userStyles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:read'])]
    private ?Style $style = null;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isPrincipal = null;

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

    public function getStyle(): ?Style
    {
        return $this->style;
    }

    public function setStyle(?Style $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function isPrincipal(): ?bool
    {
        return $this->isPrincipal;
    }

    #[Groups(['user:read'])]
    public function getIsPrincipal(): ?bool
    {
        return $this->isPrincipal;
    }

    public function setIsPrincipal(bool $isPrincipal): static
    {
        $this->isPrincipal = $isPrincipal;

        return $this;
    }

    public function __toString(): string
    {
        $str = $this->style ? $this->style->getNomStyle() : 'Style';
        if ($this->isPrincipal) $str .= ' (principal)';
        return $str;
    }
}
