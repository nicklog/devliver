<?php

declare(strict_types=1);

namespace App\Form\Type\Forms;

use App\Form\Type\Widgets\NewPasswordType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

final class UserPasswordType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('password', PasswordType::class, [
            'label'       => 'Current password',
            'attr'        => ['placeholder' => 'Current password'],
            'constraints' => [
                new UserPassword(),
            ],
        ]);
        $builder->add('passwordNew', NewPasswordType::class, [
            'first_options' => [
                'label' => 'New password',
                'attr'  => ['placeholder' => 'New password'],
            ],
            'mapped'        => false,
        ]);
    }
}
