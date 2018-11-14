<?php

declare(strict_types=1);

namespace AmsterdamPHP\Controller;

use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\ReadMeetupRepository;
use AmsterdamPHP\Service\MeetupDotCom;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class SendDetailsToMeetupDotCom
{
    /**
     * @var ReadMeetupRepository
     */
    private $meetupRepository;

    /**
     * @var MeetupDotCom
     */
    private $meetupDotCom;

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(
        ReadMeetupRepository $meetupRepository,
        MeetupDotCom $meetupDotCom,
        RouterInterface $router
    ) {
        $this->meetupRepository = $meetupRepository;
        $this->meetupDotCom = $meetupDotCom;
        $this->router = $router;
    }

    public function __invoke(Request $request) : RedirectResponse
    {
        $meetupDate = new DateTimeImmutable($request->get('date'));

        try {
            $meetup = $this->meetupRepository->getMeetupForDate($meetupDate);

            $this->meetupDotCom->send($meetup);
        } catch (UnauthorizedException $exception) {
            return new RedirectResponse($this->router->generate('authenticationWithGoogleStart'));
        } catch (AuthorizationExpiredException $exception) {
            return new RedirectResponse(
                $this->router->generate(
                    'authenticationWithGoogleRefresh',
                    [
                        'redirectBack' => 'meetupDetails',
                    ]
                )
            );
        }

        return new RedirectResponse($this->router->generate('meetupDetails', ['date' => $request->get('date')]));
    }
}
