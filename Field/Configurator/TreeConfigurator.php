<?php

namespace Umanit\EasyAdminTreeBundle\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Umanit\EasyAdminTreeBundle\Field\TreeField;

final class TreeConfigurator implements FieldConfiguratorInterface
{
    public function supports(FieldDto $field, EntityDto $entityDto): bool
    {
        return TreeField::class === $field->getFieldFqcn();
    }

    public function configure(FieldDto $field, EntityDto $entityDto, AdminContext $context): void
    {
        $targetEntityFqcn = $field->getDoctrineMetadata()->get('targetEntity');
        $field->setFormTypeOptionIfNotSet('class', $targetEntityFqcn);
    }
}
