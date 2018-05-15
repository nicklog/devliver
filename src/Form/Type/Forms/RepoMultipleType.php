<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class RepoMultipleType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 */
class RepoMultipleType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('urls', TextareaType::class, [
            'required'    => false,
            'label'       => 'Repository URLs. In each line one.',
            'constraints' => [
                new NotBlank()
            ],
            'attr'        => [
                'rows' => 10
            ]
        ]);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Save'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
        ]);
    }

}
