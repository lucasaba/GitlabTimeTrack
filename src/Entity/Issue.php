<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="issue")
 * @ORM\Entity(repositoryClass="App\Repository\IssueRepository")
 */
class Issue
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
     * @ORM\Column(type="integer")
     * @var int
     */
    private $issueNumber;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="issues")
     * @var Project|null
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Milestone", inversedBy="issues")
     * @var Milestone|null
     */
    private $milestone;

     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $timeEstimate;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $totalTimeSpent;

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
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $status;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGitlabId(): ?int
    {
        return $this->gitlabId;
    }

    public function setGitlabId(int $gitlabId): self
    {
        $this->gitlabId = $gitlabId;

        return $this;
    }

    public function getIssueNumber(): ?int
    {
        return $this->issueNumber;
    }

    public function setIssueNumber(int $issueNumber): self
    {
        $this->issueNumber = $issueNumber;

        return $this;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): void
    {
        $this->milestone = $milestone;
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

    public function getTimeEstimate(): ?int
    {
        return $this->timeEstimate;
    }

    public function setTimeEstimate(int $timeEstimate): self
    {
        $this->timeEstimate = $timeEstimate;

        return $this;
    }

    public function getTotalTimeSpent(): ?int
    {
        return $this->totalTimeSpent;
    }

    public function setTotalTimeSpent(int $totalTimeSpent): self
    {
        $this->totalTimeSpent = $totalTimeSpent;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
