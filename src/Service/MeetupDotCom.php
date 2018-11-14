<?php

declare(strict_types=1);

namespace AmsterdamPHP\Service;

use AmsterdamPHP\Model\Meetup;
use DMS\Service\Meetup\MeetupKeyAuthClient;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Twig\Environment;

class MeetupDotCom
{
    /**
     * @var MeetupKeyAuthClient
     */
    private $meetupAuthClient;

    /**
     * @var Environment
     */
    private $templating;

    public function __construct(MeetupKeyAuthClient $meetupAuthClient, Environment $templating)
    {
        $this->meetupAuthClient = $meetupAuthClient;
        $this->templating = $templating;
    }

    public function send(Meetup $meetup) : void
    {
        $meetupId = $this->findMeetupDotComId($meetup);
        if (null === $meetupId) {
            return;
        }

        $description = $this->generateDescription($meetup);

        $parameters = [
            'urlname'     => 'amsterdamphp',
            'id'          => $meetupId,
            'description' => $description,
        ];

        $venueId = $this->findMeetupDotComVenueId($meetup);
        if (null !== $venueId) {
            $parameters['venue_id'] = $venueId;
        }

        $this->meetupAuthClient->editGroupEvents(
            $parameters
        );
    }

    private function findMeetupDotComId(Meetup $meetup) : ?string
    {
        $futureEvents = $this->meetupAuthClient->getGroupEvents(['urlname' => 'amsterdamphp']);

        foreach ($futureEvents->getData() as $futureEvent) {
            if ($futureEvent['local_date'] === $meetup->getDate()->format('Y-m-d')) {
                return $futureEvent['id'];
            }
        }

        return null;
    }

    private function generateDescription(Meetup $meetup) : string
    {
        return $this->templating->render(
            'meetupDotCom/meetupDetails.html.twig',
            [
                'meetup' => $meetup
            ]
        );
    }

    private function findMeetupDotComVenueId(Meetup $meetup) : ?int
    {
        if (false === $meetup->hasHost()) {
            return null;
        }

        try {
            $response = $this->meetupAuthClient->createGroupVenues(
                [
                    'urlname'   => 'amsterdamphp',
                    'name'      => $meetup->getHost()->getName(),
                    'address_1' => $meetup->getHost()->getAddress(),
                    'city'      => 'Amsterdam',
                    'country'   => 'NL',
                ]
            );

            $responseBody = json_decode($response->getBody(true));

            return $responseBody->id;
        } catch (ClientErrorResponseException $exception) {
            if (409 === $exception->getResponse()->getStatusCode()) {
                /*
                 * The venue already exists (but is not available in the list of venues), so meetup returns a list of
                 * similar venues. We'll just use the first suggestion.
                 */
                $responseBody = json_decode($exception->getResponse()->getBody(true));
                return $responseBody->errors[0]->potential_matches[0]->id;
            }
        }

        return null;
    }
}
