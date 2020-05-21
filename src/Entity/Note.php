<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="note")
 * @ORM\Entity(repositoryClass="App\Repository\NoteRepository")
 */
class Note
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $gitlabId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Issue", inversedBy="notes")
     * @var Issue|null
     */
    private $issue;

    /**
     * @ORM\Column(type="text")
     * @var string
     */
    private $body;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $author;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $system;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTimeInterface
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @var DateTimeInterface
     */
    private $updatedAt;

    public function __construct(
        int $gitlabId,
        Issue $issue,
        string $body,
        int $author_id,
        bool $system,
        \DateTimeInterface $createdAt,
        \DateTimeInterface $updatedAt
    ) {
        $this->gitlabId = $gitlabId;
        $this->issue = $issue;
        $this->body = $body;
        $this->author = $author_id;
        $this->system = $system;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGitlabId(): ?int
    {
        return $this->gitlabId;
    }

    public function setGitlabId(int $gitlabId): void
    {
        $this->gitlabId = $gitlabId;
    }

    public function getIssue(): ?Issue
    {
        return $this->issue;
    }

    public function setIssue(?Issue $issue): void
    {
        $this->issue = $issue;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function setAuthor(int $author): void
    {
        $this->author = $author;
    }

    public function getSystem(): ?int
    {
        return $this->system;
    }

    public function setSystem(int $system): void
    {
        $this->system = $system;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
