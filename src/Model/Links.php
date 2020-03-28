<?php

namespace App\Model;

use JMS\Serializer\Annotation as JmsSerializer;

class Links
{
    /**
     * @var string|null
     * @JmsSerializer\Type("string")
     */
    protected $self;

    /**
     * @var string|null
     * @JmsSerializer\Type("string")
     */
    protected $notes;

    /**
     * @var string|null
     * @JmsSerializer\Type("string")
     */
    protected $award_emoji;

    /**
     * @var string|null
     * @JmsSerializer\Type("string")
     */
    protected $project;

    /**
     * @return string|null
     */
    public function getSelf(): ?string
    {
        return $this->self;
    }

    /**
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @return string|null
     */
    public function getAwardEmoji(): ?string
    {
        return $this->award_emoji;
    }

    /**
     * @return string|null
     */
    public function getProject(): ?string
    {
        return $this->project;
    }
}
