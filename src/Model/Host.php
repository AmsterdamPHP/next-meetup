<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

use Ramsey\Uuid\Uuid;

class Host
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

    public function __construct(Uuid $id, string $name, ?string $address, ?int $spaceLimit, ?Contact $contact)
    {
        $this->id         = $id;
        $this->name       = $name;
        $this->address    = $address;
        $this->spaceLimit = $spaceLimit;
        $this->contact    = $contact;
    }

    public function getId() : Uuid
    {
        return $this->id;
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
