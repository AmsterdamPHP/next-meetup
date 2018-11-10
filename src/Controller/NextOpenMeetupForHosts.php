<?php

declare(strict_types=1);

namespace AmsterdamPHP\Controller;

use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\MeetupRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class NextOpenMeetupForHosts
{
    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var Environment
     */
    private $templating;

    public function __construct(MeetupRepository $meetupRepository, Environment $templating)
    {
        $this->meetupRepository = $meetupRepository;
        $this->templating       = $templating;
    }

    public function __invoke() : Response
    {
        try {
            $nextMeetupWithoutHost = $this->meetupRepository->nextMeetupWithoutHost();
        } catch (UnauthorizedException $exception) {
            return new RedirectResponse($this->router->generate('authenticationWithGoogleStart'));
        } catch (AuthorizationExpiredException $exception) {
            return new RedirectResponse(
                $this->router->generate(
                    'authenticationWithGoogleRefresh',
                    [
                        'redirectBack' => 'listOfNextMeetups',
                    ]
                )
            );
        }

        return new Response(
            $this->templating->render(
                'nextOpenMeetupForHosts.html.twig',
                [
                    'meetup' => $nextMeetupWithoutHost,
                ]
            )
        );
    }
}
