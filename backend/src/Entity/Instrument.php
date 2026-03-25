<?php

namespace App\Entity;

use App\Repository\InstrumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InstrumentRepository::class)]
#[Vich\Uploadable]
#[ApiResource]
class Instrument
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(['user:read'])]
    private ?string $nom_instrument = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Vich\UploadableField(mapping: 'instruments', fileNameProperty: 'imageInstrument')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_instrument = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, UserInstrument>
     */
    #[ORM\OneToMany(targetEntity: UserInstrument::class, mappedBy: 'instrument')]
    private Collection $userInstruments;

    public function __construct()
    {
        $this->userInstruments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomInstrument(): ?string
    {
        return $this->nom_instrument;
    }

    public function setNomInstrument(string $nom_instrument): static
    {
        $this->nom_instrument = $nom_instrument;

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

    public function getImageInstrument(): ?string
    {
        return $this->image_instrument;
    }

    public function setImageInstrument(?string $image_instrument): static
    {
        $this->image_instrument = $image_instrument;

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
     * @return Collection<int, UserInstrument>
     */
    public function getUserInstruments(): Collection
    {
        return $this->userInstruments;
    }

    public function addUserInstrument(UserInstrument $userInstrument): static
    {
        if (!$this->userInstruments->contains($userInstrument)) {
            $this->userInstruments->add($userInstrument);
            $userInstrument->setInstrument($this);
        }

        return $this;
    }

    public function removeUserInstrument(UserInstrument $userInstrument): static
    {
        if ($this->userInstruments->removeElement($userInstrument)) {
            // set the owning side to null (unless already changed)
            if ($userInstrument->getInstrument() === $this) {
                $userInstrument->setInstrument(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->nom_instrument ?? '';
    }
}
