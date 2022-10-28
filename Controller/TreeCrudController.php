<?php

namespace Umanit\EasyAdminTreeBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Exception\ForbiddenActionException;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Security\Permission;

abstract class TreeCrudController extends AbstractCrudController
{

    protected ManagerRegistry $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function index(AdminContext $context)
    {
        $event = new BeforeCrudActionEvent($context);
        $this->container->get('event_dispatcher')->dispatch($event);

        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        if (!$this->isGranted(Permission::EA_EXECUTE_ACTION, ['action' => Action::INDEX, 'entity' => null])) {
            throw new ForbiddenActionException($context);
        }

        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $context->getCrud()->setFieldAssets($this->getFieldAssets($fields));
        $filters = $this->container->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());

        $repository = $this->doctrine->getRepository($context->getEntity()->getFqcn());

        $queryBuilder = $repository
            ->createQueryBuilder('entity')
            ->orderBy('entity.root, entity.lft', 'ASC')
        ;

        $this->doctrine->getManager()->getConfiguration()->addCustomHydrationMode('tree', 'Gedmo\Tree\Hydrator\ORM\TreeObjectHydrator');
        $entities = $queryBuilder->getQuery()->getResult();
        $entities = $this->container->get(EntityFactory::class)->createCollection($context->getEntity(), $entities);

        $this->container->get(EntityFactory::class)->processFieldsForAll($entities, $fields);
        $actions = $this->container->get(EntityFactory::class)->processActionsForAll($entities, $context->getCrud()->getActionsConfig());

        $responseParameters = $this->configureResponseParameters(KeyValueStore::new([
            'pageName'       => Crud::PAGE_INDEX,
            'templateName'   => 'crud/index',
            'entities'       => $entities,
            'global_actions' => $actions->getGlobalActions(),
            'batch_actions'  => null,
            'filters'        => $filters,
        ]));

        $event = new AfterCrudActionEvent($context, $responseParameters);
        $this->container->get('event_dispatcher')->dispatch($event);

        if ($event->isPropagationStopped()) {
            return $event->getResponse();
        }

        return $responseParameters;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($pageName === Crud::PAGE_INDEX) {
            return [TextField::new($this->getEntityLabelProperty())];
        }

        return [];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->overrideTemplate('crud/index', '@UmanitEasyAdminTreeBundle/index.html.twig')
            ->setPaginatorPageSize(9999999)
            ->showEntityActionsInlined()
            ->setSearchFields(null)
        ;
    }

    public static function getEntityFqcn(): string
    {
        throw new \LogicException('Override this method in child class');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addCssFile('bundles/umaniteasyadmintree/css/tree.css')
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT,
                function (Action $action) {
                    return $action
                        ->addCssClass('umanit_easyadmintree_tree-item-action')
                    ;
                }
            )
            ->update(Crud::PAGE_INDEX, Action::DELETE,
                function (Action $action) {
                    return $action
                        ->addCssClass('action-delete umanit_easyadmintree_tree-item-action')
                    ;
                }
            )
        ;
    }

    abstract protected function getEntityLabelProperty(): string;
}
