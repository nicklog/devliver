<?php

declare(strict_types=1);

namespace App\Security\Guard\Authenticator;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

use function strtr;

final class ApiAuthenticator extends AbstractGuardAuthenticator
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function supports(Request $request): bool
    {
        return $request->get('token') !== null;
    }

    public function getCredentials(Request $request): mixed
    {
        return $request->get('token');
    }

    public function getUser(mixed $credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if ($credentials === null) {
            return null;
        }

        if ($this->apiKey !== $credentials) {
            throw new BadCredentialsException();
        }

        return $userProvider->loadUserByUsername($credentials);
    }

    public function checkCredentials(mixed $credentials, UserInterface $user): bool
    {
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
