<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\AnimalRepository")
 */
class Animal
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=512)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $detail;

    /**
     * @ORM\Column(type="string", length=8)
     */
    private $sex;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $age;

    /**
     * @ORM\Column(type="text")
     */
    private $body;

   
    /**
     * @Assert\File(
     * maxSize = "1024k", 
     * mimeTypes={ "image/gif", "image/jpeg", "image/png" },
     * mimeTypesMessage = "Seuls les formats suivants sont acceptés : gif, png, jpeg"
     * )
     * 
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $picture1;

   /**
     * @Assert\File(
     * maxSize = "1024k", 
     * mimeTypes={ "image/gif", "image/jpeg", "image/png" },
     * mimeTypesMessage = "Seuls les formats suivants sont acceptés : gif, png, jpeg"
     * )
     * 
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $picture2;

    /**
     * @Assert\File(
     * maxSize = "1024k", 
     * mimeTypes={ "image/gif", "image/jpeg", "image/png" },
     * mimeTypesMessage = "Seuls les formats suivants sont acceptés : gif, png, jpeg"
     * )
     * 
     * @ORM\Column(type="string", length=512, nullable=true)
     */
    private $picture3;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="animals")
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    private $name;

    public function __construct() {
        // valeurs par défaut
        $this->isActive = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(?string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getPicture1()
    {
        return $this->picture1;
    }

    public function setPicture1($picture1): self
    {
        $this->picture1 = $picture1;

        return $this;
    }

    public function getPicture2()
    {
        return $this->picture2;
    }

    public function setPicture2($picture2): self
    {
        $this->picture2 = $picture2;

        return $this;
    }

    public function getPicture3()
    {
        return $this->picture3;
    }

    public function setPicture3($picture3): self
    {
        $this->picture3 = $picture3;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function __toString(){
        return $this->title;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

}
