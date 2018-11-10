<?php

declare(strict_types=1);

namespace AmsterdamPHP\Repository;

use AmsterdamPHP\Collection\MeetupCollection;
use AmsterdamPHP\Model\Meetup;
use DateTimeImmutable;

interface ReadMeetupRepository
{
    public function listOfNextMeetups(int $limit = 5) : MeetupCollection;

    public function nextMeetupWithoutHost() : Meetup;

    public function nextMeetupWithoutSpeaker() : Meetup;

    public function getMeetupForDate(DateTimeImmutable $date) : ?Meetup;
}
