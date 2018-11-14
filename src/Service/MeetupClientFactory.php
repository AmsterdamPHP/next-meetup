<?php

declare(strict_types=1);

namespace AmsterdamPHP\Service;

use DMS\Service\Meetup\MeetupKeyAuthClient;

class MeetupClientFactory
{
    public static function factory(string $key) : MeetupKeyAuthClient
    {
        return MeetupKeyAuthClient::factory(['key' => $key]);
    }
}
