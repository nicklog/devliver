<?php

declare(strict_types=1);

namespace App\Form\Type\Forms;

use App\Entity\Package;
use App\Form\Type\Widgets\PackageTypeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PackageType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', PackageTypeType::class, [
            'required'    => true,
            'label'       => 'Typ',
            'constraints' => [
                new NotBlank(),
            ],
        ]);

        $builder->add('url', TextType::class, [
            'required'    => false,
            'label'       => 'Repository URL or path (bitbucket git repositories need the trailing .git)',
            'constraints' => [
                new NotBlank(),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'factory' => Package::class,
        ]);
    }
}
