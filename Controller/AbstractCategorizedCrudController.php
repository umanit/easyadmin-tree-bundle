<?php

namespace Umanit\EasyAdminTreeBundle\Controller;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;

abstract class AbstractCategorizedCrudController extends AbstractCrudController
{
    public const CATEGORY_URL_PARAM_NAME = 'umanit_category';

    protected ManagerRegistry $registry;

    public function __construct(
        ManagerRegistry $registry
    ) {
        $this->registry = $registry;
    }

    abstract public static function getCategoryFqcn(): string;

    abstract protected static function getCategoryPropertyName(): string;

    abstract protected function getDefaultCategoryId(): int;

    protected function getCategoryRepository(): ObjectRepository
    {
        return $this->registry->getRepository($this->getCategoryFqcn());
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        $qb = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);

        // Fetch a list of ids of current category + ones for children categories
        $currentCategoryId = $this->getCurrentCategoryId();
        $currentCategory = $this->getCategoryRepository()->find($currentCategoryId);
        $categories = $this
            ->getCategoryRepository()
            ->childrenQueryBuilder($currentCategory, false, null, 'ASC', true)
            ->select('node.id')
            ->getQuery()
            ->getResult(Query::HYDRATE_SCALAR_COLUMN)
        ;

        // Apply category filter only if there is no search or other filter activated
        if ('' === $searchDto->getQuery() && empty($filters->all())) {
            $qb
                ->andWhere('entity.'.$this->getCategoryPropertyName().' in (:categories)')
                ->setParameter('categories', $categories)
            ;
        }

        return $qb;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        return KeyValueStore::new(array_merge($responseParameters->all(), [
            'context'          => $this->getContext(),
            'current_category' => $this->getCurrentCategoryId(),
        ]));
    }

    protected function getCurrentCategoryId(): int
    {
        return $this->getContext()->getRequest()->get(self::CATEGORY_URL_PARAM_NAME) ?? $this->getDefaultCategoryId();
    }

    public function configureCrud(Crud $crud): Crud
    {
        $crud
            ->renderContentMaximized()
            ->overrideTemplate('crud/index', '@UmanitEasyAdminTreeBundle/categorized-crud/index.html.twig')
        ;

        return $crud;
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('bundles/umaniteasyadmintree/css/categorized-tree.css')
        ;
    }
}
