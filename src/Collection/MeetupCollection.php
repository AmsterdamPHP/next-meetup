<?php

declare(strict_types=1);

namespace AmsterdamPHP\Collection;

use AmsterdamPHP\Model\Meetup;
use IteratorAggregate;

class MeetupCollection implements IteratorAggregate
{
    /**
     * @var Meetup[]
     */
    private $meetups;

    public function __construct(Meetup ...$meetup)
    {
        $this->meetups = $meetup;
    }

    public static function fromArray(array $meetups) : self
    {
        return new self(...$meetups);
    }

    public function addMeetup(Meetup $meetup) : void
    {
        $this->meetups[] = $meetup;
    }

    public function filter(callable $callback) : self
    {
        return self::fromArray(array_filter($this->meetups, $callback));
    }

    public function count() : int
    {
        return count($this->meetups);
    }

    public function slice(int $offset, int $limit) : self
    {
        return self::fromArray(array_slice($this->meetups, $offset, $limit));
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->meetups);
    }

    public function first() : Meetup
    {
        return reset($this->meetups);
    }
}
