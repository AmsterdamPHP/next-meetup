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

    public static function fromContactString(string $contactString) : self
    {
        $contactContents = null;
        preg_match(
            '/(\w+(?:\s+\w+)*)\'?\s+<?(\S+@[\w.-]+\.[a-zA-Z]{2,4}\b)>?/',
            $contactString,
            $contactContents
        );

        if ([] === $contactContents) {
            $contactContents = [1 => $contactString, 2 => null];
        }

        return new Contact(
            trim($contactContents[1]),
            $contactContents[2]
        );
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
