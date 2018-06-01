<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Shapecode\Devliver\Entity\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PackageAbandonType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class PackageAbandonType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('replacementPackage', TextType::class, [
            'label' => 'Replacement package',
            'required' => false,
            'attr'  => [
                'placeholder' => 'optional package name'
            ]
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
        $resolver->setDefaults([
            'data_class' => Package::class
        ]);
    }

}
