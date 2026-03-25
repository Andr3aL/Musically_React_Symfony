<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Enum\Civility;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[Vich\Uploadable]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    normalizationContext: ['groups' => ['user:read']]
)]
#[ApiFilter(SearchFilter::class, properties: ['email' => 'exact'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['band:read', 'user:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Groups(['user:read'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Groups(['band:read', 'user:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 50)]
    #[Groups(['band:read', 'user:read'])]
    private ?string $lastName = null;

    #[Vich\UploadableField(mapping: 'users', fileNameProperty: 'image')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['band:read', 'user:read'])]
    private ?string $image = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $birthday = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['band:read', 'user:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['band:read', 'user:read'])]
    private ?string $country = null;

    #[ORM\Column(type: 'string', nullable: true, enumType: Civility::class)]
    private ?Civility $civility = null;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'sender')]
    private Collection $sentMessages;

    /**
     * @var Collection<int, Message>
     */
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'receiver')]
    private Collection $receivedMessages;

    /**
     * @var Collection<int, UserStyle>
     */
    #[ORM\OneToMany(targetEntity: UserStyle::class, mappedBy: 'user')]
    #[Groups(['user:read'])]
    private Collection $userStyles;

    /**
     * @var Collection<int, UserInstrument>
     */
    #[ORM\OneToMany(targetEntity: UserInstrument::class, mappedBy: 'user')]
    #[Groups(['user:read'])]
    private Collection $userInstruments;

    /**
     * @var Collection<int, BandMember>
     */
    #[ORM\OneToMany(targetEntity: BandMember::class, mappedBy: 'user')]
    private Collection $bandMemberships;

    public function __construct()
    {
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
        $this->userStyles = new ArrayCollection();
        $this->userInstruments = new ArrayCollection();
        $this->bandMemberships = new ArrayCollection();
    }

    public function getCivility(): ?Civility
    {
        return $this->civility;
    }

    public function setCivility(?Civility $civility): static
    {
        $this->civility = $civility;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
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

    public function getBirthday(): ?\DateTime
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTime $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function addSentMessage(Message $sentMessage): static
    {
        if (!$this->sentMessages->contains($sentMessage)) {
            $this->sentMessages->add($sentMessage);
            $sentMessage->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Message $sentMessage): static
    {
        if ($this->sentMessages->removeElement($sentMessage)) {
            // set the owning side to null (unless already changed)
            if ($sentMessage->getSender() === $this) {
                $sentMessage->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }

    public function addReceivedMessage(Message $receivedMessage): static
    {
        if (!$this->receivedMessages->contains($receivedMessage)) {
            $this->receivedMessages->add($receivedMessage);
            $receivedMessage->setReceiver($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Message $receivedMessage): static
    {
        if ($this->receivedMessages->removeElement($receivedMessage)) {
            // set the owning side to null (unless already changed)
            if ($receivedMessage->getReceiver() === $this) {
                $receivedMessage->setReceiver(null);
            }
        }

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
            $userStyle->setUser($this);
        }

        return $this;
    }

    public function removeUserStyle(UserStyle $userStyle): static
    {
        if ($this->userStyles->removeElement($userStyle)) {
            // set the owning side to null (unless already changed)
            if ($userStyle->getUser() === $this) {
                $userStyle->setUser(null);
            }
        }

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
            $userInstrument->setUser($this);
        }

        return $this;
    }

    public function removeUserInstrument(UserInstrument $userInstrument): static
    {
        if ($this->userInstruments->removeElement($userInstrument)) {
            // set the owning side to null (unless already changed)
            if ($userInstrument->getUser() === $this) {
                $userInstrument->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BandMember>
     */
    public function getBandMemberships(): Collection
    {
        return $this->bandMemberships;
    }

    public function addBandMembership(BandMember $bandMembership): static
    {
        if (!$this->bandMemberships->contains($bandMembership)) {
            $this->bandMemberships->add($bandMembership);
            $bandMembership->setUser($this);
        }

        return $this;
    }

    public function removeBandMembership(BandMember $bandMembership): static
    {
        if ($this->bandMemberships->removeElement($bandMembership)) {
            // set the owning side to null (unless already changed)
            if ($bandMembership->getUser() === $this) {
                $bandMembership->setUser(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}
