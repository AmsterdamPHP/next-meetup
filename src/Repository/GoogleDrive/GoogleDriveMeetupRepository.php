<?php

declare(strict_types=1);

namespace AmsterdamPHP\Repository\GoogleDrive;

use AmsterdamPHP\Collection\MeetupCollection;
use AmsterdamPHP\Model\Host;
use AmsterdamPHP\Model\Meetup;
use AmsterdamPHP\Model\Speaker;
use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\MeetupRepository;
use DateTimeImmutable;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\Exception\WorksheetNotFoundException;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\SpreadsheetService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GoogleDriveMeetupRepository implements MeetupRepository
{
    /**
     * @var SessionInterface
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @throws AuthorizationExpiredException
     * @throws UnauthorizedException
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     */
    public function listOfNextMeetups(int $limit = 5) : MeetupCollection
    {
        $thisYear          = (int)(new DateTimeImmutable())->format('Y');
        $listOfNextMeetups = $this->listOfNextMeetupsInYear(new MeetupCollection(), $limit, $thisYear);

        // Limit meetups from the start
        $listOfNextMeetups = $listOfNextMeetups->slice(0, $limit);

        return $listOfNextMeetups;
    }

    public function nextMeetupWithoutHost() : Meetup
    {
        $thisYear          = (int)(new DateTimeImmutable())->format('Y');
        $listOfNextMeetups = $this->listOfNextMeetupsInYear(new MeetupCollection(), null, $thisYear);

        return $listOfNextMeetups
            ->filter(function(Meetup $meetup) : bool {
                return false === $meetup->hasHost();
            })
            ->first();
    }

    public function nextMeetupWithoutSpeaker() : Meetup
    {
        $thisYear          = (int)(new DateTimeImmutable())->format('Y');
        $listOfNextMeetups = $this->listOfNextMeetupsInYear(new MeetupCollection(), null, $thisYear);

        return $listOfNextMeetups
            ->filter(function(Meetup $meetup) : bool {
                return false === $meetup->hasSpeaker();
            })
            ->first();
    }

    /**
     * @throws AuthorizationExpiredException
     * @throws UnauthorizedException
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     */
    private function listOfNextMeetupsInYear(
        MeetupCollection $listOfNextMeetups,
        ?int $limit,
        int $year
    ) : MeetupCollection {
        $spreadsheet = $this->getMonthlyMeetingsSpreadsheet();

        try {
            $worksheet = $spreadsheet->getWorksheetByTitle((string)$year);
        } catch (WorksheetNotFoundException $e) {
            return $listOfNextMeetups;
        }

        $cellFeed = $worksheet->getCellFeed();
        $rowCount = $worksheet->getRowCount();

        // Find meetups
        for ($i = 2; $i <= $rowCount; $i++) {
            $dateCell = $cellFeed->getCell($i, 1);

            if (null === $dateCell) {
                continue;
            }

            $hostCell    = $cellFeed->getCell($i, 3);
            $speakerCell = $cellFeed->getCell($i, 7);

            $listOfNextMeetups->addMeetup(
                new Meetup(
                    new DateTimeImmutable($dateCell->getContent()),
                    $hostCell ? new Host($hostCell->getContent()) : null,
                    $speakerCell ? new Speaker($speakerCell->getContent()) : null
                )
            );
        }

        // Filter meetups after today
        $listOfNextMeetups = $listOfNextMeetups->filter(
            function (Meetup $meetup) : bool {
                return $meetup->isAfterDate(new DateTimeImmutable());
            }
        );

        if (null !== $limit && $listOfNextMeetups->count() >= $limit) {
            return $listOfNextMeetups;
        }

        return $this->listOfNextMeetupsInYear($listOfNextMeetups, $limit, $year + 1);
    }

    /**
     * @throws AuthorizationExpiredException
     * @throws UnauthorizedException
     * @throws \Google\Spreadsheet\Exception\SpreadsheetNotFoundException
     */
    private function getMonthlyMeetingsSpreadsheet() : Spreadsheet
    {
        if (false === $this->session->has('googleAccessToken')) {
            throw new UnauthorizedException();
        }

        try {
            $accessToken = $this->session->get('googleAccessToken');

            $serviceRequest = new DefaultServiceRequest($accessToken);
            ServiceRequestFactory::setInstance($serviceRequest);

            $spreadsheetService = new SpreadsheetService();
            $spreadsheetFeed    = $spreadsheetService->getSpreadsheetFeed();

            return $spreadsheetFeed->getByTitle('Monthly Meetings');
        } catch (UnauthorizedException $exception) {
            throw new AuthorizationExpiredException();
        }
    }
}
