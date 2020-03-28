<?php

namespace App\Model;

use JMS\Serializer\Annotation as JmsSerializer;

class References
{
    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $short;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $relative;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $full;

    /**
     * @return string
     */
    public function getShort(): string
    {
        return $this->short;
    }

    /**
     * @return string
     */
    public function getRelative(): string
    {
        return $this->relative;
    }

    /**
     * @return string
     */
    public function getFull(): string
    {
        return $this->full;
    }
}
