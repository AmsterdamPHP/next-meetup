<?php

declare(strict_types=1);

namespace AmsterdamPHP\Repository;

use AmsterdamPHP\Model\Meetup;

interface WriteMeetupRepository
{
    public function store(Meetup $meetup) : void;
}
