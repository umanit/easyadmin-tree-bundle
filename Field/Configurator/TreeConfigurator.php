<?php

namespace Umanit\EasyAdminTreeBundle\Field\Configurator;

use Doctrine\ORM\EntityRepository;
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
		$field->setFormTypeOptionIfNotSet('query_builder', static function (EntityRepository $repository) use ($field) {
			// TODO: should this use `createIndexQueryBuilder` instead, so we get the default ordering etc.?
			// it would then be identical to the one used in autocomplete action, but it is a bit complex getting it in here
			if (null !== $queryBuilderCallable = $field->getCustomOption(TreeField::OPTION_QUERY_BUILDER_CALLABLE)) {
				$queryBuilder = $queryBuilderCallable($repository);
			} else {
				$queryBuilder = $repository->createQueryBuilder('entity');
			}

			return $queryBuilder;
		});
    }
}
