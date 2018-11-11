<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

class Host
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $address;

    /**
     * @var int|null
     */
    private $spaceLimit;

    /**
     * @var Contact
     */
    private $contact;

    public function __construct(string $name, ?string $address, ?int $spaceLimit, ?Contact $contact)
    {
        $this->name       = $name;
        $this->address    = $address;
        $this->spaceLimit = $spaceLimit;
        $this->contact    = $contact;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getAddress() : ?string
    {
        return $this->address;
    }

    public function getSpaceLimit() : ?int
    {
        return $this->spaceLimit;
    }

    public function getContact() : ?Contact
    {
        return $this->contact;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
