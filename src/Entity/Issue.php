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

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="issue")
     * @var ArrayCollection<Note>
     */
    private $notes;

    /**
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

    public function __construct(
        string $title,
        int $gitlabId,
        int $issueNumber,
        Project $project,
        DateTimeInterface $createdAt,
        DateTimeInterface $updatedAt,
        string $status,
        int $timeEstimate,
        int $totalTimeSpent
    ) {
        $this->notes = new ArrayCollection();

        $this->title = $title;
        $this->gitlabId = $gitlabId;
        $this->issueNumber = $issueNumber;
        $this->project = $project;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->status = $status;
        $this->timeEstimate = $timeEstimate;
        $this->totalTimeSpent = $totalTimeSpent;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGitlabId(): ?int
    {
        return $this->gitlabId;
    }

    public function getIssueNumber(): ?int
    {
        return $this->issueNumber;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): void
    {
        $this->milestone = $milestone;
    }

    /**
     * @return ArrayCollection<Note>
     */
    public function getNotes()
    {
        return $this->notes;
    }

    public function addNote(Note $note): void
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setIssue($this);
        }
    }

    public function removeNote(Note $note): void
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getIssue() === $this) {
                $note->setIssue(null);
            }
        }
    }

    public function clearNotes(): void
    {
        $this->notes->clear();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getTimeEstimate(): ?int
    {
        return $this->timeEstimate;
    }

    public function getTotalTimeSpent(): ?int
    {
        return $this->totalTimeSpent;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function update(
        string $status,
        DateTimeInterface $updatedAt,
        int $timeEstimate,
        int $totalTimeSpent
    ): void  {
        $this->status = $status;
        $this->updatedAt = $updatedAt;
        $this->timeEstimate = $timeEstimate;
        $this->totalTimeSpent = $totalTimeSpent;
    }
}
