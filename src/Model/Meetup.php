<?php

declare(strict_types=1);

namespace AmsterdamPHP\Model;

use DateTimeImmutable;

class Meetup
{
    /**
     * @var DateTimeImmutable
     */
    private $date;

    /**
     * @var Host|null
     */
    private $host;

    /**
     * @var Speaker|null
     */
    private $speaker;

    public function __construct(DateTimeImmutable $date, ?Host $host, ?Speaker $speaker)
    {
        $this->date = $date;
        $this->host = $host;
        $this->speaker = $speaker;
    }

    public function getDate() : DateTimeImmutable
    {
        return $this->date;
    }

    public function getHost() : ?Host
    {
        return $this->host;
    }

    public function hasHost() : bool
    {
        return null !== $this->host;
    }

    public function planInHost(Host $host) : self
    {
        return new self($this->date, $host, $this->speaker);
    }

    public function getSpeaker() : ?Speaker
    {
        return $this->speaker;
    }

    public function hasSpeaker() : bool
    {
        return null !== $this->speaker;
    }

    public function planInSpeaker(Speaker $speaker) : self
    {
        return new self($this->date, $this->host, $speaker);
    }

    public function isAfterDate(DateTimeImmutable $date) : bool
    {
        return $date < $this->date;
    }
}
