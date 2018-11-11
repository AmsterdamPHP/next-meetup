<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

class Speaker
{
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

    public function __construct(string $name, ?string $contactInformation, ?string $biography, ?Talk $talk)
    {
        $this->name               = $name;
        $this->contactInformation = $contactInformation;
        $this->biography          = $biography;
        $this->talk               = $talk;
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
