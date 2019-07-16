<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Shapecode\Devliver\Form\Type\Widgets\SetupDatabaseType;
use Shapecode\Devliver\Model\Setup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SetupType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 */
class SetupType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('database', SetupDatabaseType::class, []);

        $builder->add('submit', SubmitType::class, [
            'label' => 'Save',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Setup::class,
            'attr'       => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }

}
