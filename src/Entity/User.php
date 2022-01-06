<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
    implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=36)
     */
    private $msId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastName;

    /**
     * @ORM\OneToMany(targetEntity=FMessage::class, mappedBy="owner_id")
     */
    private $fMessages;

    /**
     * @ORM\OneToMany(targetEntity=FMessage::class, mappedBy="friend_id")
     */
    private $fReceivedMessages;

    /**
     * @ORM\OneToMany(targetEntity=Question::class, mappedBy="author")
     */
    private $questions;

    public function __construct()
    {
        $this->fMessages = new ArrayCollection();
        $this->fReceivedMessages = new ArrayCollection();
        $this->questions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getMsId(): ?string
    {
        return $this->msId;
    }

    public function setMsId(string $msId): self
    {
        $this->msId = $msId;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function jsonSerialize()
    {
        return [
            'msId'      => $this->msId,
            'firstName' => $this->firstName,
            'lastName'  => $this->lastName,
            'email'     => $this->email,
            'username'  => $this->username ?? '__NULL__',
        ];
    }

    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setAuthor($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->removeElement($question)) {
            // set the owning side to null (unless already changed)
            if ($question->getAuthor() === $this) {
                $question->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FMessage[]
     */
    public function getFMessages(): Collection
    {
        return $this->fMessages;
    }

    public function addFMessage(FMessage $fMessage): self
    {
        if (!$this->fMessages->contains($fMessage)) {
            $this->fMessages[] = $fMessage;
            $fMessage->setOwner($this);
        }

        return $this;
    }

    public function removeFMessage(FMessage $fMessage): self
    {
        if ($this->fMessages->removeElement($fMessage)) {
            // set the owning side to null (unless already changed)
            if ($fMessage->getOwner() === $this) {
                $fMessage->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|FMessage[]
     */
    public function getFReceivedMessages(): Collection
    {
        return $this->fReceivedMessages;
    }

    public function addFReceivedMessage(FMessage $fReceivedMessage): self
    {
        if (!$this->fReceivedMessages->contains($fReceivedMessage)) {
            $this->fReceivedMessages[] = $fReceivedMessage;
            $fReceivedMessage->setFriend($this);
        }

        return $this;
    }

    public function removeFReceivedMessage(FMessage $fReceivedMessage): self
    {
        if ($this->fReceivedMessages->removeElement($fReceivedMessage)) {
            // set the owning side to null (unless already changed)
            if ($fReceivedMessage->getFriend() === $this) {
                $fReceivedMessage->setFriend(null);
            }
        }

        return $this;
    }
}
