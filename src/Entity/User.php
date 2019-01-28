<?php
// src/AppBundle/Entity/User.php
namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity("username", message="Identifiant déjà utilisé")
 * @UniqueEntity("email", message="Adresse email déjà utilisée")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=254, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=256)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=16)
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $city;

    /**
     * @ORM\Column(type="float")
     */
    private $longitude;

    /**
     * @ORM\Column(type="float")
     */
    private $latitude;

    /**
     * @Assert\File(
     * maxSize = "1024k", 
     * mimeTypes={ "image/gif", "image/jpeg", "image/png" },
     * mimeTypesMessage = "Seuls les formats suivants sont acceptés : gif, png, jpeg"
     * )
     * 
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $phoneNumber;

    /**
     * @ORM\Column(type="string", length=16, nullable=true)
     */
    private $cellNumber;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidated;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $connectedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Role", inversedBy="users")
     */
    private $role;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="owner")
     */
    private $commentsOwner;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="petsitter")
     */
    private $commentsPetsitter;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="userFrom")
     */
    private $messagesFrom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="userTo")
     */
    private $messagesTo;

    public function __construct()
    {
        $this->isActive = true;
        $this->isValidated = false;
        $this->createdAt = new DateTime();

        // $this->comments = new ArrayCollection();
        // $this->messages = new ArrayCollection();
        $this->messagesFrom = new ArrayCollection();
        $this->messagesTo = new ArrayCollection();
        $this->commentsOwner = new ArrayCollection();
        $this->commentsPetsitter = new ArrayCollection();
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getRoles()
    {
        $role = $this->role->getCode();

        $roleArray = [
            $role
        ];

        // Cette méthode doit obligatoirement
        // retourner un array
        return $roleArray;

        // return ['ROLE_USER'];
    }

    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ]);
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized, ['allowed_classes' => false]);
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getCellNumber(): ?string
    {
        return $this->cellNumber;
    }

    public function setCellNumber(?string $cellNumber): self
    {
        $this->cellNumber = $cellNumber;

        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getConnectedAt(): ?\DateTimeInterface
    {
        return $this->connectedAt;
    }

    public function setConnectedAt(?\DateTimeInterface $connectedAt): self
    {
        $this->connectedAt = $connectedAt;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword($password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function __toString(){
        return $this->username;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessagesFrom(): Collection
    {
        return $this->messagesFrom;
    }

    public function addMessagesFrom(Message $messagesFrom): self
    {
        if (!$this->messagesFrom->contains($messagesFrom)) {
            $this->messagesFrom[] = $messagesFrom;
            $messagesFrom->setUserFrom($this);
        }

        return $this;
    }

    public function removeMessagesFrom(Message $messagesFrom): self
    {
        if ($this->messagesFrom->contains($messagesFrom)) {
            $this->messagesFrom->removeElement($messagesFrom);
            // set the owning side to null (unless already changed)
            if ($messagesFrom->getUserFrom() === $this) {
                $messagesFrom->setUserFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessagesTo(): Collection
    {
        return $this->messagesTo;
    }

    public function addMessagesTo(Message $messagesTo): self
    {
        if (!$this->messagesTo->contains($messagesTo)) {
            $this->messagesTo[] = $messagesTo;
            $messagesTo->setUserTo($this);
        }

        return $this;
    }

    public function removeMessagesTo(Message $messagesTo): self
    {
        if ($this->messagesTo->contains($messagesTo)) {
            $this->messagesTo->removeElement($messagesTo);
            // set the owning side to null (unless already changed)
            if ($messagesTo->getUserTo() === $this) {
                $messagesTo->setUserTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getCommentsOwner(): Collection
    {
        return $this->commentsOwner;
    }

    public function addCommentsOwner(Comment $commentsOwner): self
    {
        if (!$this->commentsOwner->contains($commentsOwner)) {
            $this->commentsOwner[] = $commentsOwner;
            $commentsOwner->setCommentsOwner($this);
        }

        return $this;
    }

    public function removeCommentsOwner(Comment $commentsOwner): self
    {
        if ($this->commentsOwner->contains($commentsOwner)) {
            $this->commentsOwner->removeElement($commentsOwner);
            // set the owning side to null (unless already changed)
            if ($commentsOwner->getCommentsOwner() === $this) {
                $commentsOwner->setCommentsOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getCommentsPetsitter(): Collection
    {
        return $this->commentsPetsitter;
    }

    public function addCommentsPetsitter(Comment $commentsPetsitter): self
    {
        if (!$this->commentsPetsitter->contains($commentsPetsitter)) {
            $this->commentsPetsitter[] = $commentsPetsitter;
            $commentsPetsitter->setCommentsPetsitter($this);
        }

        return $this;
    }

    public function removeCommentsPetsitter(Comment $commentsPetsitter): self
    {
        if ($this->commentsPetsitter->contains($commentsPetsitter)) {
            $this->commentsPetsitter->removeElement($commentsPetsitter);
            // set the owning side to null (unless already changed)
            if ($commentsPetsitter->getCommentsPetsitter() === $this) {
                $commentsPetsitter->setCommentsPetsitter(null);
            }
        }

        return $this;
    }

}