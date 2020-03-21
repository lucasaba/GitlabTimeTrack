<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Issue
 *
 * @ORM\Table(name="issue")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\IssueRepository")
 */
class Issue
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="gitlabId", type="integer")
     */
    private $gitlabId;

    /**
     * @var int
     *
     * @ORM\Column(name="issue_number", type="integer")
     */
    private $issueNumber;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project", inversedBy="issues")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     */
    private $project;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var int
     *
     * @ORM\Column(name="timeEstimate", type="integer")
     */
    private $timeEstimate;

    /**
     * @var int
     *
     * @ORM\Column(name="totalTimeSpent", type="integer")
     */
    private $totalTimeSpent;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updatedAt", type="datetime")
     */
    private $updatedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    private $status;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gitlabId
     *
     * @param integer $gitlabId
     *
     * @return Issue
     */
    public function setGitlabId($gitlabId)
    {
        $this->gitlabId = $gitlabId;

        return $this;
    }

    /**
     * Get gitlabId
     *
     * @return int
     */
    public function getGitlabId()
    {
        return $this->gitlabId;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Issue
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set timeEstimate
     *
     * @param integer $timeEstimate
     *
     * @return Issue
     */
    public function setTimeEstimate($timeEstimate)
    {
        $this->timeEstimate = $timeEstimate;

        return $this;
    }

    /**
     * Get timeEstimate
     *
     * @return int
     */
    public function getTimeEstimate()
    {
        return $this->timeEstimate;
    }

    /**
     * Set totalTimeSpent
     *
     * @param integer $totalTimeSpent
     *
     * @return Issue
     */
    public function setTotalTimeSpent($totalTimeSpent)
    {
        $this->totalTimeSpent = $totalTimeSpent;

        return $this;
    }

    /**
     * Get totalTimeSpent
     *
     * @return int
     */
    public function getTotalTimeSpent()
    {
        return $this->totalTimeSpent;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Issue
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Issue
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     *
     * @return Issue
     */
    public function setProject(\AppBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \AppBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set issueNumber
     *
     * @param integer $issueNumber
     *
     * @return Issue
     */
    public function setIssueNumber($issueNumber)
    {
        $this->issueNumber = $issueNumber;

        return $this;
    }

    /**
     * Get issueNumber
     *
     * @return integer
     */
    public function getIssueNumber()
    {
        return $this->issueNumber;
    }
}
