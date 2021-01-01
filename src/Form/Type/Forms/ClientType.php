<?php

declare(strict_types=1);

namespace App\Form\Type\Forms;

use App\Entity\Client;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

final class ClientType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label'       => 'Name',
            'constraints' => [
                new NotBlank(),
                new Regex([
                    'pattern' => '/^[a-z0-9-_]{1,255}$/i',
                    'message' => 'Your name must contain only letter, numbers, dash and underscore and must have length between 1 and 255',
                ]),
                new Length([
                    'min' => 2,
                    'max' => 255,
                ]),
            ],
        ]);

        $builder->add('enable', CheckboxType::class, [
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'factory'     => Client::class,
            'constraints' => [
                new UniqueEntity([
                    'fields' => ['name'],
                ]),
            ],
        ]);
    }
}
