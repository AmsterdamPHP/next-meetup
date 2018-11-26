<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

use Ramsey\Uuid\Uuid;

class Speaker
{
    /**
     * @var Uuid
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $contactInformation;

    /**
     * @var string|null
     */
    private $biography;

    /**
     * @var Talk|null
     */
    private $talk;

    public function __construct(Uuid $id, string $name, ?string $contactInformation, ?string $biography, ?Talk $talk)
    {
        $this->id                 = $id;
        $this->name               = $name;
        $this->contactInformation = $contactInformation;
        $this->biography          = $biography;
        $this->talk               = $talk;
    }

    public function getId() : Uuid
    {
        return $this->id;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getContactInformation() : ?string
    {
        return $this->contactInformation;
    }

    public function getBiography() : ?string
    {
        return $this->biography;
    }

    public function getTalk() : ?Talk
    {
        return $this->talk;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
