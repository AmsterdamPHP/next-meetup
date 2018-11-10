<?php

declare(strict_types=1);

namespace AmsterdamPHP\Controller;

use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\MeetupRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class NextOpenMeetupForSpeakers
{
    /**
     * @var MeetupRepository
     */
    private $meetupRepository;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Environment
     */
    private $templating;

    public function __construct(MeetupRepository $meetupRepository, RouterInterface $router, Environment $templating)
    {
        $this->meetupRepository = $meetupRepository;
        $this->router           = $router;
        $this->templating       = $templating;
    }

    public function __invoke() : Response
    {
        try {
            $nextMeetupWithoutSpeaker = $this->meetupRepository->nextMeetupWithoutSpeaker();
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
                'nextOpenMeetupForSpeakers.html.twig',
                [
                    'meetup' => $nextMeetupWithoutSpeaker,
                ]
            )
        );
    }
}
