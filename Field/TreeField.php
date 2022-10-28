<?php

namespace Umanit\EasyAdminTreeBundle\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Umanit\EasyAdminTreeBundle\Form\Type\TreeFieldType;

class TreeField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_CLASS = 'class';

    public static function new(string $propertyName, ?string $label = null)
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->addFormTheme('@UmanitEasyAdminTreeBundle/form/themes/tree.html.twig')
            ->setFormType(TreeFieldType::class)
            ->addCssFiles('bundles/umaniteasyadmintree/css/tree-field.css')
        ;
    }
}
