<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FilterType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 * @company tenolo GbR
 */
class FilterType extends AbstractType
{

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', TextType::class, [
            'label' => false,
            'attr'  => [
                'placeholder' => 'Filter'
            ]
        ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method'          => 'GET',
            'csrf_protection' => false
        ]);
    }

}
