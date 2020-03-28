<?php

namespace App\Model;

use JMS\Serializer\Annotation as JmsSerializer;

class TaskCompletionStatus
{
    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $count;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $completed_count;

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return int
     */
    public function getCompletedCount(): int
    {
        return $this->completed_count;
    }
}
