<?php

declare(strict_types=1);

namespace App\Form\Type\Forms;

use App\Entity\Package;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageAbandonType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('replacementPackage', TextType::class, [
            'label'    => 'Replacement package',
            'required' => false,
            'attr'     => [
                'placeholder' => 'optional package name',
            ],
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Package::class,
        ]);
    }
}
