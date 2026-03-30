<?php

namespace App\Entity;

use App\Repository\StyleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StyleRepository::class)]
#[Vich\Uploadable]
#[ApiResource]
class Style
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'band:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read', 'band:read'])]
    private ?string $nom_style = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Vich\UploadableField(mapping: 'styles', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, UserStyle>
     */
    #[ORM\OneToMany(targetEntity: UserStyle::class, mappedBy: 'style')]
    private Collection $userStyles;

    public function __construct()
    {
        $this->userStyles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomStyle(): ?string
    {
        return $this->nom_style;
    }

    public function setNomStyle(string $nom_style): static
    {
        $this->nom_style = $nom_style;
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

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
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
     * @return Collection<int, UserStyle>
     */
    public function getUserStyles(): Collection
    {
        return $this->userStyles;
    }

    public function addUserStyle(UserStyle $userStyle): static
    {
        if (!$this->userStyles->contains($userStyle)) {
            $this->userStyles->add($userStyle);
            $userStyle->setStyle($this);
        }
        return $this;
    }

    public function removeUserStyle(UserStyle $userStyle): static
    {
        if ($this->userStyles->removeElement($userStyle)) {
            if ($userStyle->getStyle() === $this) {
                $userStyle->setStyle(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->nom_style ?? '';
    }
}