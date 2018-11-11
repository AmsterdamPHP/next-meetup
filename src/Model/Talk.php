<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

class Talk
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $abstract;

    public function __construct(string $title, ?string $abstract)
    {
        $this->title = $title;
        $this->abstract = $abstract;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function getAbstract() : ?string
    {
        return $this->abstract;
    }
}
