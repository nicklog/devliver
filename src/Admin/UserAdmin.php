<?php

namespace Shapecode\Devliver\Admin;

use Shapecode\Devliver\Entity\Version;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

/**
 * Class UserAdmin
 *
 * @package Shapecode\Devliver\Admin
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class UserAdmin extends \Sonata\UserBundle\Admin\Entity\UserAdmin
{

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);

        $formMapper
            ->tab('Packages')
                ->with('Settings')
                    ->add('packageRootAccess', null, ['required' => false])
                    ->add('autoAddToNewPackages', null, ['required' => false])
                    ->add('autoAddToNewVersions', null, ['required' => false])
                ->end()
                ->with('Versions')
                    ->add('accessVersions', EntityType::class, [
                        'class' => Version::class,
                        'required' => false,
                        'multiple' => true,
                        'expanded' => true,
                    ])
                ->end()
            ->end();
    }
}
