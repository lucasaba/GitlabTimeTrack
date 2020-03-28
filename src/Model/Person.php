<?php

namespace App\Model;

use JMS\Serializer\Annotation as JmsSerializer;

class Person
{
    /**
     * @var int
     * @JmsSerializer\Type("int")
     */
    protected $id;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $name;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $username;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $state;

    /**
     * @var string
     * @JmsSerializer\Type("string")
     */
    protected $avatar_url;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getAvatarUrl(): string
    {
        return $this->avatar_url;
    }

    /**
     * @return string
     */
    public function getWebUrl(): string
    {
        return $this->web_url;
    }
}