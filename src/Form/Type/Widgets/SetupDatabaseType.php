<?php

namespace Shapecode\Devliver\Form\Type\Widgets;

use Shapecode\Devliver\Model\SetupDatabase;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class SetupDatabaseType
 *
 * @package Shapecode\Devliver\Form\Type\Widgets
 * @author  Nikita Loges
 * @company tenolo GmbH & Co. KG
 */
class SetupDatabaseType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('host', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);
        $builder->add('port', IntegerType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);
        $builder->add('name', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);
        $builder->add('username', TextType::class, [
            'constraints' => [
                new NotBlank(),
            ],
        ]);
        $builder->add('password', TextType::class, []);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SetupDatabase::class,
        ]);
    }

}
