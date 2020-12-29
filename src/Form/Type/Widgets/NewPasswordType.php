<?php

declare(strict_types=1);

namespace App\Form\Type\Widgets;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;

final class NewPasswordType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'first_options'  => [
                'label' => false,
                'attr'  => ['placeholder' => 'New password'],
            ],
            'second_options' => [
                'label' => false,
                'attr'  => ['placeholder' => 'Repeat new password'],
            ],
            'type'           => PasswordType::class,
            'constraints'    => [
                new NotBlank(),
                new NotCompromisedPassword(),
            ],
        ]);
    }

    public function getParent(): string
    {
        return RepeatedType::class;
    }
}
