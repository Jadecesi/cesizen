<?php

// src/Security/ApiTokenAuthenticator.php
namespace App\Security;

use App\Repository\UtilisateurRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function __construct(private UtilisateurRepository $users) {}

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), '/api')
            && $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): Passport
    {
        if (!preg_match('/^Bearer\s+(.+)$/', $request->headers->get('Authorization', ''), $m)) {
            throw new AuthenticationException('Token missing');
        }
        $token = $m[1];

        return new SelfValidatingPassport(new UserBadge($token, function ($token) {
            return $this->users->findOneByValidApiToken($token);
        }));
    }

    public function onAuthenticationFailure(Request $r, AuthenticationException $e): ?JsonResponse
    {
        return new JsonResponse(['error' => $e->getMessage()], 401);
    }

    public function onAuthenticationSuccess(Request $r, $t, string $f): ?JsonResponse
    {
        return null; // la requête continue
    }
}

