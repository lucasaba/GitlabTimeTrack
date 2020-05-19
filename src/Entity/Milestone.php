<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="milestone")
 * @ORM\Entity(repositoryClass="App\Repository\MilestoneRepository")
 */
class Milestone
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="issues")
     * @var Project|null
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $state;

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

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTimeInterface
     */
    private $dueAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTimeInterface
     */
    private $startAt;

    public function __construct(
        int $gitlabId,
        Project $project,
        string $title,
        DateTimeInterface $createdAt,
        DateTimeInterface $updatedAt,
        ?DateTimeInterface $dueAt,
        ?DateTimeInterface $startAt,
        string $state,
        string $description)
    {
        $this->gitlabId = $gitlabId;
        $this->updatedAt = $updatedAt;
        $this->createdAt = $createdAt;
        $this->title = $title;
        $this->project = $project;
        $this->dueAt = $dueAt;
        $this->startAt = $startAt;
        $this->state = $state;
        $this->description = $description;
    }

    public function update(
        string $state,
        string $title,
        string $description,
        DateTimeInterface $updatedAt,
        DateTimeInterface $dueAt,
        DateTimeInterface $startAt
    ): void
    {
        $this->updatedAt = $updatedAt;
        $this->title = $title;
        $this->dueAt = $dueAt;
        $this->startAt = $startAt;
        $this->state = $state;
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGitlabId(): ?int
    {
        return $this->gitlabId;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getDueAt(): ?DateTimeInterface
    {
        return $this->dueAt;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
