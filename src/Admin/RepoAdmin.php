<?php

namespace Shapecode\Devliver\Admin;

use Shapecode\Devliver\Entity\User;
use Shapecode\Devliver\Form\Type\Widgets\RepoTypeType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RepoAdmin
 *
 * @package Shapecode\Devliver\Admin
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class RepoAdmin extends AbstractAdmin
{

    /**
     * @inheritdoc
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->add('type', RepoTypeType::class, [
            'required'    => true,
            'label'       => 'Typ',
            'constraints' => [
                new NotBlank()
            ]
        ]);
        $formMapper->add('url', TextType::class, [
            'required'    => true,
            'label'       => 'Repository URL or path (bitbucket git repositories need the trailing .git)',
            'constraints' => [
                new NotBlank()
            ]
        ]);
        $formMapper->add('creator', EntityType::class, [
            'class'    => User::class,
            'required' => false,
        ]);

        $formMapper->add('enable', CheckboxType::class, [
            'required' => false,
        ]);

        $formMapper->add('abandoned', CheckboxType::class, [
            'required' => false,
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('url');
        $datagridMapper->add('package.name');
    }

    /**
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper->addIdentifier('id');
        $listMapper->addIdentifier('url');
        $listMapper->add('type');
        $listMapper->add('enable', null, [
            'editable' => true
        ]);
        $listMapper->add('abandoned', null, [
            'editable' => true
        ]);
        $listMapper->add('creator');
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper->add('id');
        $showMapper->add('url');
        $showMapper->add('type');
        $showMapper->add('creator');
        $showMapper->add('enable');
        $showMapper->add('abandoned');
    }
}
