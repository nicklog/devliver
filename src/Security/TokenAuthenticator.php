<?php

declare(strict_types=1);

namespace App\Security;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

use function get_class;
use function sprintf;
use function strtr;

class TokenAuthenticator implements AuthenticationFailureHandlerInterface
{
    /**
     * @inheritdoc
     */
    public function createToken(Request $request, $providerKey)
    {
        // look for an apikey query parameter
        $token  = $request->get('token');
        $token2 = $request->headers->get('token');

        if ($token2 && ! $token) {
            $token = $token2;
        }

        if (! $token) {
            return null;
        }

        return new PreAuthenticatedToken(
            'anon.',
            $token,
            $providerKey
        );
    }

    /**
     * @inheritdoc
     */
    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    /**
     * @inheritdoc
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        if (! $userProvider instanceof TokenUserProvider) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user provider must be an instance of TokenUserProvider (%s was given).',
                    get_class($userProvider)
                )
            );
        }

        $token    = $token->getCredentials();
        $username = $userProvider->getUsernameForToken($token);

        if (! $username) {
            throw new BadCredentialsException();
        }

        $user = $userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $token,
            $providerKey,
            $user->getRoles()
        );
    }

    /**
     * @inheritdoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response(
            strtr($exception->getMessageKey(), $exception->getMessageData()),
            401
        );
    }
}
