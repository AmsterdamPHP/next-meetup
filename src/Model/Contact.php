<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

class Contact
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $email;

    public function __construct(string $name, ?string $email)
    {
        $this->name  = $name;
        $this->email = $email;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }
}
