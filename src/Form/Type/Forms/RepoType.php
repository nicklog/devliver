<?php

namespace Shapecode\Devliver\Form\Type\Forms;

use Shapecode\Devliver\Entity\RepoInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RepoType
 *
 * @package Shapecode\Devliver\Form\Type\Forms
 * @author  Nikita Loges
 */
class RepoType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('repo', \Shapecode\Devliver\Form\Type\Widgets\RepoType::class, [
            'inherit_data' => true,
            'label' => false,
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
            'data_class' => RepoInterface::class
        ]);
    }

}
