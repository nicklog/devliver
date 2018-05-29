<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Shapecode\Devliver\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class UserProfileType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 */
class UserProfileType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('username', TextType::class, [
            'label'       => 'Username',
            'constraints' => [
                new NotBlank()
            ]
        ]);
        $builder->add('email', EmailType::class, [
            'label'       => 'Email',
            'constraints' => [
                new NotBlank()
            ]
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
