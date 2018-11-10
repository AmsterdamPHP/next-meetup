<?php

declare(strict_types=1);

namespace AmsterdamPHP\Controller;

use AmsterdamPHP\Repository\GoogleDrive\Exception\AuthorizationExpiredException;
use AmsterdamPHP\Repository\GoogleDrive\Exception\UnauthorizedException;
use AmsterdamPHP\Repository\ReadMeetupRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class MeetupDetails
{
    /**
     * @var ReadMeetupRepository
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

    public function __construct(
        ReadMeetupRepository $meetupRepository,
        RouterInterface $router,
        Environment $templating
    ) {
        $this->meetupRepository = $meetupRepository;
        $this->router = $router;
        $this->templating = $templating;
    }

    public function __invoke(Request $request) : Response
    {
        $meetupDate = new DateTimeImmutable($request->get('date'));

        try {
            $meetup = $this->meetupRepository->getMeetupForDate($meetupDate);
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

        return new Response(
            $this->templating->render(
                'meetupDetails.html.twig',
                [
                    'meetup' => $meetup
                ]
            )
        );
    }
}
