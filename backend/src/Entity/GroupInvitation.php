<?php

namespace App\Entity;

use App\Repository\GroupInvitationRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GroupInvitationRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['invitation:read']]
)]
class GroupInvitation
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invitation:read'])]
    private ?User $sender = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['invitation:read'])]
    private ?User $receiver = null;

    #[ORM\Column(length: 20)]
    #[Groups(['invitation:read'])]
    private ?string $status = self::STATUS_PENDING;

    #[ORM\Column]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['invitation:read'])]
    private ?\DateTimeImmutable $respondedAt = null;

    #[ORM\ManyToOne(targetEntity: Band::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['invitation:read'])]
    private ?Band $createdBand = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): static
    {
        $this->receiver = $receiver;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getRespondedAt(): ?\DateTimeImmutable
    {
        return $this->respondedAt;
    }

    public function setRespondedAt(?\DateTimeImmutable $respondedAt): static
    {
        $this->respondedAt = $respondedAt;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function accept(): void
    {
        $this->status = self::STATUS_ACCEPTED;
        $this->respondedAt = new \DateTimeImmutable();
    }

    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->respondedAt = new \DateTimeImmutable();
    }

    public function getCreatedBand(): ?Band
    {
        return $this->createdBand;
    }

    public function setCreatedBand(?Band $createdBand): static
    {
        $this->createdBand = $createdBand;
        return $this;
    }
}
