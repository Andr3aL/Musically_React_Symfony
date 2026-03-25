<?php

namespace App\Entity;

use App\Repository\BandRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: BandRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    normalizationContext: ['groups' => ['band:read']]
)]
class Band
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['band:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['band:read'])]
    private ?string $nameBand = null;

    #[ORM\Column]
    #[Groups(['band:read'])]
    private ?\DateTime $dateCreation = null;

    #[Vich\UploadableField(mapping: 'bands', fileNameProperty: 'photoBand')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['band:read'])]
    private ?string $photoBand = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, BandMember>
     */
    #[ORM\OneToMany(targetEntity: BandMember::class, mappedBy: 'band')]
    #[Groups(['band:read'])]
    private Collection $members;

    #[ORM\Column]
    #[Groups(['band:read'])]
    private bool $needsSetup = false;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['band:read'])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Style::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['band:read'])]
    private ?Style $style = null;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNameBand(): ?string
    {
        return $this->nameBand;
    }

    public function setNameBand(string $nameBand): static
    {
        $this->nameBand = $nameBand;

        return $this;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->dateCreation;
    }

    public function setDateCreation(\DateTime $dateCreation): static
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getPhotoBand(): ?string
    {
        return $this->photoBand;
    }

    public function setPhotoBand(?string $photoBand): static
    {
        $this->photoBand = $photoBand;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, BandMember>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(BandMember $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setBand($this);
        }

        return $this;
    }

    public function removeMember(BandMember $member): static
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getBand() === $this) {
                $member->setBand(null);
            }
        }

        return $this;
    }

    public function isNeedsSetup(): bool
    {
        return $this->needsSetup;
    }

    public function setNeedsSetup(bool $needsSetup): static
    {
        $this->needsSetup = $needsSetup;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function __toString(): string
    {
        return $this->nameBand ?? '';
    }
}
