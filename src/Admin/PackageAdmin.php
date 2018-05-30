<?php

namespace Shapecode\Devliver\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;

/**
 * Class PackageAdmin
 *
 * @package Shapecode\Devliver\Admin
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class PackageAdmin extends AbstractAdmin
{

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
        $collection->remove('edit');
        $collection->remove('delete');
    }

    /**
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper->add('name');
    }

    /**
     * @inheritdoc
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        unset($this->listModes['mosaic']);

        $listMapper->addIdentifier('id');
        $listMapper->addIdentifier('name');
        $listMapper->add('lastUpdate');
        $listMapper->add('autoUpdate', null, [
            'editable' => true
        ]);
    }
}
