<?php

declare(strict_types=1);

namespace App\Infrastructure\ArgumentResolver;

use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class UserValueResolver implements ParamConverterInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === User::class;
    }

    public function apply(Request $request, ParamConverter $configuration): bool
    {
        if (! $this->security->getUser() instanceof User) {
            return false;
        }

        if ($request->attributes->has($configuration->getName())) {
            return false;
        }

        $request->attributes->set(
            $configuration->getName(),
            $this->security->getUser()
        );

        return true;
    }
}
