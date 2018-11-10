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

    public function __construct(string $name, ?string $contactInformation)
    {
        $this->name               = $name;
        $this->contactInformation = $contactInformation;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getContactInformation() : ?string
    {
        return $this->contactInformation;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
