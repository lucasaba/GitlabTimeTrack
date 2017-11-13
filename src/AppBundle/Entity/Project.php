<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Project
 *
 * @ORM\Table(name="project")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 */
class Project
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", length=255, nullable=true)
     */
    private $namespace;

    /**
     * @var int
     *
     * @ORM\Column(name="gitlab_id", type="integer", unique=true)
     */
    private $gitlabId;

    /**
     * @var string
     *
     * @ORM\Column(name="avatar_url", type="string", length=255, nullable=true)
     */
    private $avatarUrl;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Issue", mappedBy="project", cascade={"remove"})
     */
    private $issues;


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
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set gitlabId
     *
     * @param integer $gitlabId
     *
     * @return Project
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
     * Set avatarUrl
     *
     * @param string $avatarUrl
     *
     * @return Project
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    /**
     * Get avatarUrl
     *
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->issues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add issue
     *
     * @param \AppBundle\Entity\Issue $issue
     *
     * @return Project
     */
    public function addIssue(\AppBundle\Entity\Issue $issue)
    {
        $this->issues[] = $issue;

        return $this;
    }

    /**
     * Remove issue
     *
     * @param \AppBundle\Entity\Issue $issue
     */
    public function removeIssue(\AppBundle\Entity\Issue $issue)
    {
        $this->issues->removeElement($issue);
    }

    /**
     * Get issues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssues()
    {
        return $this->issues;
    }
}
