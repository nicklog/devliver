<?php

declare(strict_types=1);

namespace App\Form\Type\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('package', \App\Form\Type\Widgets\PackageType::class, [
            'inherit_data' => true,
            'label'        => false,
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Save',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }
}
