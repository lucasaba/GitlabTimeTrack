<?php

namespace App\Model;

use DateTimeInterface;
use JMS\Serializer\Annotation as JmsSerializer;

class Milestone
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
     * @var int
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
    protected $due_date;

    /**
     * @var DateTimeInterface|null
     * @JmsSerializer\Type("DateTime<'Y-m-d\TH:i:s.uP'>")
     */
    protected $start_date;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $web_url;

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
     * @return int
     */
    public function getProjectId(): int
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
     * @return int
     */
    public function getDescription(): int
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
    public function getDueDate(): ?DateTimeInterface
    {
        return $this->due_date;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getStartDate(): ?DateTimeInterface
    {
        return $this->start_date;
    }

    /**
     * @return string
     */
    public function getWebUrl(): string
    {
        return $this->web_url;
    }
}