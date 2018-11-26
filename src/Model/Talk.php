<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

use Ramsey\Uuid\Uuid;

class Talk
{
    /**
     * @var Uuid
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string|null
     */
    private $abstract;

    public function __construct(Uuid $id, string $title, ?string $abstract)
    {
        $this->id = $id;
        $this->title = $title;
        $this->abstract = $abstract;
    }

    public function getId() : Uuid
    {
        return $this->id;
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
