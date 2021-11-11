<?php

namespace App\Security\Authenticator;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;

class FormAuthenticator extends AbstractAuthenticator
{
    private const LOGIN_ROUTE = 'security_login';
    private const SUCCESS_ROUTE = 'home_index';

    public function __construct
    (
        private UrlGeneratorInterface $urlGenerator,
    )
    {}

    public function supports(Request $request): ?bool
    {
        return
            $request->isMethod(Request::METHOD_POST) &&
            self::LOGIN_ROUTE === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): PassportInterface
    {
        return new Passport(
            new UserBadge($request->request->get('email')),
            new PasswordCredentials($request->request->get('password')),
            [
                new CsrfTokenBadge('psy_authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate(self::SUCCESS_ROUTE));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set(Security::AUTHENTICATION_ERROR, 'Invalid credentials');
        }
        return new RedirectResponse($this->urlGenerator->generate(self::LOGIN_ROUTE));
    }
}