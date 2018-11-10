<?php

declare(strict_types=1);

namespace AmsterdamPHP\Repository\GoogleDrive;

use AmsterdamPHP\Collection\MeetupCollection;
use AmsterdamPHP\Model\Contact;
use AmsterdamPHP\Model\Host;
use AmsterdamPHP\Model\Meetup;
use AmsterdamPHP\Model\Speaker;
use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\ReadMeetupRepository;
use AmsterdamPHP\Repository\WriteMeetupRepository;
use DateTimeImmutable;
use Google\Spreadsheet\DefaultServiceRequest;
use Google\Spreadsheet\Exception\WorksheetNotFoundException;
use Google\Spreadsheet\Exception\UnauthorizedException as GoogleUnauthorizedException;
use Google\Spreadsheet\ServiceRequestFactory;
use Google\Spreadsheet\Spreadsheet;
use Google\Spreadsheet\SpreadsheetService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class GoogleDriveMeetupRepository implements ReadMeetupRepository, WriteMeetupRepository
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
    public function listOfNextMeetups(int $limit = 12) : MeetupCollection
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

    public function getMeetupForDate(DateTimeImmutable $date) : ?Meetup
    {
        $thisYear          = (int)(new DateTimeImmutable())->format('Y');
        $listOfNextMeetups = $this->listOfNextMeetupsInYear(new MeetupCollection(), null, $thisYear);

        return $listOfNextMeetups
            ->filter(function(Meetup $meetup) use ($date): bool {
                return $date->format('Y-m-d') === $meetup->getDate()->format('Y-m-d');
            })
            ->first();
    }

    public function store(Meetup $meetup) : void
    {
        $spreadsheet = $this->getMonthlyMeetingsSpreadsheet();
        $meetupYear  = $meetup->getDate()->format('Y');
        $worksheet   = $spreadsheet->getWorksheetByTitle($meetupYear);

        $cellFeed = $worksheet->getCellFeed();
        $rowNumer = ((int) $meetup->getDate()->format('n')) + 1;

        if (null !== $meetup->getHost()) {
            $cellFeed->editCell($rowNumer, 3, $meetup->getHost()->getName());
            $cellFeed->editCell($rowNumer, 4, $meetup->getHost()->getAddress());

            if (null !== $meetup->getHost()->getContact()) {
                $cellFeed->editCell(
                    $rowNumer,
                    5,
                    sprintf(
                        '%s <%s>',
                        $meetup->getHost()->getContact()->getName(),
                        $meetup->getHost()->getContact()->getEmail()
                    )
                );
            }
        }

        if (null !== $meetup->getSpeaker()) {
            $cellFeed->editCell($rowNumer, 7, $meetup->getSpeaker()->getName());

            if (null !== $meetup->getSpeaker()->getContactInformation()) {
                $cellFeed->editCell($rowNumer, 8, $meetup->getSpeaker()->getContactInformation());
            }
        }
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

            $hostCell           = $cellFeed->getCell($i, 3);
            $contactCell        = $cellFeed->getCell($i, 5);
            $speakerCell        = $cellFeed->getCell($i, 7);
            $speakerContactCell = $cellFeed->getCell($i, 8);

            $host = null;
            $contact = null;
            $speaker = null;

            if (null !== $contactCell) {
                $contact = new Contact(
                    $contactCell->getContent(),
                    null
                );
            }

            if (null !== $hostCell) {
                $addressCell = $cellFeed->getCell($i, 4);

                $host = new Host(
                    $hostCell->getContent(),
                    $addressCell ? $addressCell->getContent() : null,
                    $contact
                );
            }

            if (null !== $speakerCell) {
                $speaker = new Speaker(
                    $speakerCell->getContent(),
                    $speakerContactCell ? $speakerContactCell->getContent() : null
                );
            }

            $listOfNextMeetups->addMeetup(
                new Meetup(
                    new DateTimeImmutable($dateCell->getContent()),
                    $host,
                    $speaker
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
        } catch (GoogleUnauthorizedException $exception) {
            throw new AuthorizationExpiredException();
        }
    }
}
