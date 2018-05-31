<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 */
class PackageType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('package', \Shapecode\Devliver\Form\Type\Widgets\PackageType::class, [
            'inherit_data' => true,
            'label'        => false,
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Save'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

}
