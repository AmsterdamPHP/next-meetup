<?php

declare(strict_types=1);

namespace AmsterdamPHP\Controller;

use Google_Client;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AuthenticateWithGoogle
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var Google_Client
     */
    private $googleClient;

    public function __construct(SessionInterface $session, RouterInterface $router, Google_Client $googleClient)
    {
        $this->session = $session;
        $this->router = $router;
        $this->googleClient = $googleClient;
    }

    public function start() : Response
    {
        $this->googleClient->addScope(\Google_Service_Drive::DRIVE);
        $this->googleClient->addScope('email');
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setIncludeGrantedScopes(true);
        $this->googleClient->setRedirectUri(
            $this->router->generate('authenticationWithGoogleEnd', [], RouterInterface::ABSOLUTE_URL)
        );

        $authenticationUrl = $this->googleClient->createAuthUrl();

        return new RedirectResponse($authenticationUrl);
    }

    public function end(Request $request) : Response
    {
        $authenticationCode = $request->get('code');

        $this->googleClient->setRedirectUri(
            $this->router->generate('authenticationWithGoogleEnd', [], RouterInterface::ABSOLUTE_URL)
        );
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setIncludeGrantedScopes(true);

        $accessToken = $this->googleClient->fetchAccessTokenWithAuthCode($authenticationCode);

        $email = $this->extractEmailFromAccessToken($accessToken);
        $this->ensureEmailDomainIsAllowed($email);

        $this->session->set('googleAccessToken', $accessToken['access_token']);

        if ($this->googleClient->getRefreshToken()) {
            $this->session->set('googleRefreshToken', $this->googleClient->getRefreshToken());
        }

        return new RedirectResponse($this->router->generate('listOfNextMeetups'));
    }

    public function refresh(Request $request) : Response
    {
        $refreshToken = $this->session->get('googleRefreshToken');

        $this->googleClient->setRedirectUri(
            $this->router->generate('authenticationWithGoogleEnd', [], RouterInterface::ABSOLUTE_URL)
        );
        $this->googleClient->setAccessType('offline');
        $this->googleClient->setIncludeGrantedScopes(true);

        try {
            $accessToken = $this->googleClient->fetchAccessTokenWithRefreshToken($refreshToken);
        } catch (\LogicException $exception) {
            return new RedirectResponse(
                $this->router->generate('authenticationWithGoogleStart')
            );
        }

        $this->session->set('googleAccessToken', $accessToken['access_token']);

        if ($this->googleClient->getRefreshToken()) {
            $this->session->set('googleRefreshToken', $this->googleClient->getRefreshToken());
        }

        if ($request->query->has('redirectBack')) {
            return new RedirectResponse($this->router->generate($request->query->get('redirectBack')));
        }

        return new Response(print_r($accessToken, true));
    }

    private function extractEmailFromAccessToken($accessToken) : string
    {
        $verifiedId = $this->googleClient->verifyIdToken($accessToken['id_token']);

        return $verifiedId['email'];
    }

    private function ensureEmailDomainIsAllowed(string $email) : void
    {
        if (substr_compare($email, 'amsterdamphp.nl', -strlen('amsterdamphp.nl')) === 0) {
            return;
        }

        throw new AccessDeniedException('Access is only allowed for AmsterdamPHP people');
    }
}
