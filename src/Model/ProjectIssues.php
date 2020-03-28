<?php

namespace App\Model;

use Doctrine\Common\Collections\ArrayCollection;

class ProjectIssues
{
    /**
     * @var ArrayCollection<Issue>
     */
    protected $issues;

    public function __construct()
    {
        $this->issues = new ArrayCollection();
    }

    /**
     * @return ArrayCollection<Issue>
     */
    public function getIssues(): ArrayCollection
    {
        return $this->issues;
    }

    /**
     * @param Issue $issue
     */
    public function addIssue(Issue $issue): void
    {
        if (! $this->issues->contains($issue)) {
            $this->issues->add($issue);
        }
    }
}
