<?php

namespace Shapecode\Devliver\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator, FactoryInterface $factory)
    {
        $this->translator = $translator;
        $this->factory = $factory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu()
    {
        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'class' => 'navbar-nav mr-auto'
            ]
        ]);

        $repoTranslation = $this->translator->trans('menu.repositories');
        $menu->addChild('devliver_repo_index', [
            'label'  => '<i class="fab fa-git fa-fw" data-fa-mask="fas fa-circle"></i> ' . $repoTranslation,
            'route'  => 'devliver_repo_index',
            'extras' => [
                'routes' => [
                    'devliver_repo_create_multiple',
                    'devliver_repo_edit',
                ],
            ]
        ]);

        $menu->addChild('devliver_package_index', [
            'label'      => 'menu.packages',
            'route'      => 'devliver_package_index',
            'attributes' => [
                'icon' => 'fas fa-cube fa-fw',
            ],
            'extras'     => [
                'routes' => [
                    'devliver_package_view',
                ],
            ]
        ]);

        $menu->addChild('devliver_usage', [
            'label'      => 'menu.usage',
            'route'      => 'devliver_usage',
            'attributes' => [
                'icon' => 'fas fa-question-circle fa-fw',
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
                'class' => 'navbar-nav ml-auto'
            ]
        ]);

        $menu->addChild('fos_user_change_password', [
            'label'      => 'menu.change_password',
            'route'      => 'fos_user_change_password',
            'attributes' => [
                'icon' => 'fas fa-key fa-fw',
            ],
        ]);

        $menu->addChild('fos_user_security_logout', [
            'label'      => 'menu.logout',
            'route'      => 'fos_user_security_logout',
            'attributes' => [
                'icon' => 'fas fa-sign-out-alt fa-fw',
            ],
        ]);

        return $menu;
    }
}
