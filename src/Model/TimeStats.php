<?php

namespace App\Model;

use JMS\Serializer\Annotation as JmsSerializer;

class TimeStats
{
    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $time_estimate;

    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $total_time_spent;

    /**
     * @var int|null
     * @JmsSerializer\Type("int")
     */
    protected $human_time_estimate;

    /**
     * @var int|null
     * @JmsSerializer\Type("int")
     */
    protected $human_total_time_spent;

    /**
     * @return int
     */
    public function getTimeEstimate(): int
    {
        return $this->time_estimate;
    }

    /**
     * @return int
     */
    public function getTotalTimeSpent(): int
    {
        return $this->total_time_spent;
    }

    /**
     * @return int|null
     */
    public function getHumanTimeEstimate(): ?int
    {
        return $this->human_time_estimate;
    }

    /**
     * @return int|null
     */
    public function getHumanTotalTimeSpent(): ?int
    {
        return $this->human_total_time_spent;
    }
}

