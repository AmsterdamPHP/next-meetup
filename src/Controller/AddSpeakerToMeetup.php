<?php

declare(strict_types=1);

namespace AmsterdamPHP\Controller;

use AmsterdamPHP\Model\Contact;
use AmsterdamPHP\Model\Host;
use AmsterdamPHP\Model\Speaker;
use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\ReadMeetupRepository;
use AmsterdamPHP\Repository\WriteMeetupRepository;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

class AddSpeakerToMeetup
{
    /**
     * @var ReadMeetupRepository
     */
    private $readMeetupRepository;

    /**
     * @var WriteMeetupRepository
     */
    private $writeMeetupRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        ReadMeetupRepository $readMeetupRepository,
        WriteMeetupRepository $writeMeetupRepository,
        RouterInterface $router
    ) {
        $this->readMeetupRepository  = $readMeetupRepository;
        $this->writeMeetupRepository = $writeMeetupRepository;
        $this->router                = $router;
    }

    public function __invoke(Request $request) : Response
    {
        $meetupDate = new DateTimeImmutable($request->get('date'));

        try {
            $meetup = $this->readMeetupRepository->getMeetupForDate($meetupDate);

            $speaker = new Speaker(
                Uuid::uuid4(),
                $request->get('speaker-name'),
                $request->get('speaker-contact'),
                null,
                null
            );

            $this->writeMeetupRepository->store($meetup->planInSpeaker($speaker));
        } catch (UnauthorizedException $exception) {
            return new RedirectResponse($this->router->generate('authenticationWithGoogleStart'));
        } catch (AuthorizationExpiredException $exception) {
            return new RedirectResponse(
                $this->router->generate(
                    'authenticationWithGoogleRefresh',
                    [
                        'redirectBack' => 'nextMeetupWithoutHost',
                    ]
                )
            );
        }

        return new RedirectResponse($this->router->generate('listOfNextMeetups'));
    }
}
