<?php

declare(strict_types=1);

namespace App\Form\Type\Forms;

use App\Domain\Role;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class UserType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('email', EmailType::class, [
            'label'       => 'Mail address',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('password', PasswordType::class, [
            'required' => false,
            'mapped'   => false,
            'label'    => 'Password',
        ]);

        $builder->add('roles', ChoiceType::class, [
            'required' => false,
            'multiple' => true,
            'choices'  => [
                'Admin' => Role::ADMIN,
            ],
        ]);

        $builder->add('enable', CheckboxType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'factory'     => User::class,
            'constraints' => [
                new UniqueEntity([
                    'fields' => ['email'],
                ]),
            ],
        ]);
    }
}
