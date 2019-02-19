<?php

namespace Shapecode\Devliver\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Builder
 *
 * @package Shapecode\Devliver\Menu
 * @author  Nikita Loges
 */
class Builder
{

    /** @var TranslatorInterface */
    protected $translator;

    /** @var TranslatorInterface */
    protected $factory;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /**
     * @param TranslatorInterface           $translator
     * @param FactoryInterface              $factory
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        TranslatorInterface $translator,
        FactoryInterface $factory,
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->factory = $factory;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu()
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'navbar-nav mr-auto',
            ],
        ]);

        $menu->addChild('devliver_package_index', [
            'label'      => 'menu.packages',
            'route'      => 'devliver_package_index',
            'attributes' => [
                'icon' => 'fas fa-cube fa-fw',
            ],
            'extras'     => [
                'routes' => [
                    'devliver_package_add',
                    'devliver_package_edit',
                    'devliver_package_view',
                ],
            ],
        ]);

        return $menu;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function rightNavbar()
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'navbar-nav',
            ],
        ]);

        $token = $this->tokenStorage->getToken();
        $username = ($token !== null) ? $token->getUsername() : 'Profil';

        $dropdown = $menu->addChild(
            $username,
            [
                'attributes' => [
                    'dropdown' => true,
                    'icon'     => 'fas fa-user fa-fw',
                ],
            ]
        );

        $dropdown->addChild('devliver_profile_index', [
            'label'      => 'Profile',
            'route'      => 'devliver_profile_index',
            'attributes' => [
                'icon' => 'fas fa-user fa-fw',
            ],
        ]);

        $dropdown->addChild('fos_user_change_password', [
            'label'      => 'menu.change_password',
            'route'      => 'fos_user_change_password',
            'attributes' => [
                'divider_append' => true,
                'icon'           => 'fas fa-key fa-fw',
            ],
        ]);

        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $dropdown->addChild('sonata_admin_dashboard', [
                'label'      => 'menu.admin',
                'route'      => 'sonata_admin_dashboard',
                'attributes' => [
                    'divider_append' => true,
                    'icon'           => 'fas fa-lock fa-fw',
                ],
            ]);
        }

        $dropdown->addChild('fos_user_security_logout', [
            'label'      => 'menu.logout',
            'route'      => 'fos_user_security_logout',
            'attributes' => [
                'icon' => 'fas fa-sign-out-alt fa-fw',
            ],
        ]);

        return $menu;
    }
}
