<?php

namespace Umanit\EasyAdminTreeBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TreeFieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'expanded'      => true,
            'block_name'    => 'umanit_easyadmin_tree',
            'query_builder' => function (EntityRepository $er) {
                return $er
                    ->createQueryBuilder('entity')
                    ->orderBy('entity.root, entity.lft', 'ASC')
                ;
            },
            'choice_attr'   => function ($choice, $key, $value) {
                return ['data-level' => $choice->getLevel(), 'data-has-child' => !$choice->getChildren()->isEmpty()];
            },
            'placeholder'   => 'umanit.easyadmin.tree.form-field.placeholder',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}