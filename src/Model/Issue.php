<?php


namespace App\Model;

use DateTimeInterface;
use JMS\Serializer\Annotation as JmsSerializer;

class Issue
{
    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $id;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $iid;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $project_id;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $title;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $description;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $state;

    /**
     * @var DateTimeInterface
     * @JmsSerializer\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    protected $created_at;

    /**
     * @var DateTimeInterface
     * @JmsSerializer\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    protected $updated_at;

    /**
     * @var DateTimeInterface|null
     * @JmsSerializer\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    protected $closed_at;

    /**
     * @var Person|null
     * @JmsSerializer\Type("App\Model\Person")
     */
    protected $closed_by;

    /**
     * @var array<string>
     * @JmsSerializer\Type("array<string>")
     */
    protected $labels;

    /**
     * @var Milestone
     * @JmsSerializer\Type("App\Model\Milestone")
     */
    protected $milestone;

    /**
     * @var array<Person>
     * @JmsSerializer\Type("array<App\Model\Person>")
     */
    protected $assignees;

    /**
     * @var Person
     * @JmsSerializer\Type("App\Model\Person")
     */
    protected $author;

    /**
     * @var Person
     * @JmsSerializer\Type("App\Model\Person")
     */
    protected $assignee;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $user_notes_count;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $merge_requests_count;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $upvotes;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $downvotes;

    /**
     * @var DateTimeInterface|null
     * @JmsSerializer\Type("DateTime<'Y-m-d'>")
     */
    protected $due_date;

    /**
     * @var bool
     * @JmsSerializer\Type("bool")
     */
    protected $confidential;

    /**
     * @var bool|null
     * @JmsSerializer\Type("bool")
     */
    protected $discussion_locked;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $web_url;

    /**
     * @var TimeStats
     * @JmsSerializer\Type("App\Model\TimeStats")
     */
    protected $time_stats;

    /**
     * @var TaskCompletionStatus
     * @JmsSerializer\Type("App\Model\TaskCompletionStatus")
     */
    protected $task_completion_status;

    /**
     * @var bool
     * @JmsSerializer\Type("bool")
     */
    protected $has_tasks;

    /**
     * @var Links
     * @JmsSerializer\SerializedName("_links")
     * @JmsSerializer\Type("App\Model\Links")
     */
    protected $links;

    /**
     * @var References
     * @JmsSerializer\Type("App\Model\References")
     */
    protected $references;

    /**
     * @var int|null
     * @JmsSerializer\Type("int")
     */
    protected $moved_to_id;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getIid(): int
    {
        return $this->iid;
    }

    /**
     * @return mixed
     */
    public function getProjectId()
    {
        return $this->project_id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface
    {
        return $this->created_at;
    }

    /**
     * @return DateTimeInterface
     */
    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updated_at;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getClosedAt(): ?DateTimeInterface
    {
        return $this->closed_at;
    }

    /**
     * @return Person|null
     */
    public function getClosedBy(): ?Person
    {
        return $this->closed_by;
    }

    /**
     * @return array
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return Milestone
     */
    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    /**
     * @return array
     */
    public function getAssignees(): array
    {
        return $this->assignees;
    }

    /**
     * @return Person
     */
    public function getAuthor(): Person
    {
        return $this->author;
    }

    /**
     * @return Person
     */
    public function getAssignee(): Person
    {
        return $this->assignee;
    }

    /**
     * @return int
     */
    public function getUserNotesCount(): int
    {
        return $this->user_notes_count;
    }

    /**
     * @return mixed
     */
    public function getMergeRequestsCount()
    {
        return $this->merge_requests_count;
    }

    /**
     * @return int
     */
    public function getUpvotes(): int
    {
        return $this->upvotes;
    }

    /**
     * @return mixed
     */
    public function getDownvotes()
    {
        return $this->downvotes;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDueDate(): ?DateTimeInterface
    {
        return $this->due_date;
    }

    /**
     * @return bool
     */
    public function isConfidential(): bool
    {
        return $this->confidential;
    }

    /**
     * @return bool|null
     */
    public function getDiscussionLocked(): ?bool
    {
        return $this->discussion_locked;
    }

    /**
     * @return string
     */
    public function getWebUrl(): string
    {
        return $this->web_url;
    }

    /**
     * @return TimeStats
     */
    public function getTimeStats(): TimeStats
    {
        return $this->time_stats;
    }

    /**
     * @return TaskCompletionStatus
     */
    public function getTaskCompletionStatus(): TaskCompletionStatus
    {
        return $this->task_completion_status;
    }

    /**
     * @return bool
     */
    public function isHasTasks(): bool
    {
        return $this->has_tasks;
    }

    /**
     * @return Links
     */
    public function getLinks(): Links
    {
        return $this->links;
    }

    /**
     * @return References
     */
    public function getReferences(): References
    {
        return $this->references;
    }

    /**
     * @return int|null
     */
    public function getMovedToId(): ?int
    {
        return $this->moved_to_id;
    }
}
