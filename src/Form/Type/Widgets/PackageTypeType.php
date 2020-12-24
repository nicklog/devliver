<?php

declare(strict_types=1);

namespace App\Form\Type\Widgets;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'required'          => true,
            'label'             => 'Type',
            'choices'           => [
                'vcs'           => 'vcs',
                'gitlab'        => 'gitlab',
                'github'        => 'github',
                'git-bitbucket' => 'git-bitbucket',
//                'git'      => 'git',
//                'hg'       => 'hg',
//                'svn'      => 'svn',
//                'artifact' => 'artifact',
//                'pear'     => 'pear',
//                'package'  => 'package'
            ],
            'choices_as_values' => true,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
