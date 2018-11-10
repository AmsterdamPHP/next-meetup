<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

class Host
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function __toString() : string
    {
        return $this->name;
    }
}
