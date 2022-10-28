<?php

namespace Umanit\EasyAdminTreeBundle\Twig;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class TreeRuntime implements RuntimeExtensionInterface
{
    private Environment $twig;

    private ManagerRegistry $registry;

    private AdminUrlGenerator $urlGenerator;

    public function __construct(
        Environment $twig,
        ManagerRegistry $registry,
        AdminUrlGenerator $urlGenerator
    ) {
        $this->twig = $twig;
        $this->registry = $registry;
        $this->urlGenerator = $urlGenerator;
    }

    public function renderCategorizationSidebar(AdminContext $context, int $currentCategoryId): string
    {
        $crudControllerFqcn = $context->getCrud()->getControllerFqcn();
        $categoryFqcn = $crudControllerFqcn::getCategoryFqcn();
        $categoryRepository = $this->registry->getRepository($categoryFqcn);
        $categories = $categoryRepository->childrenHierarchy();
        $url = $this->urlGenerator
            ->setController($crudControllerFqcn)
            ->setAction(Action::INDEX)
            ->generateUrl()
        ;

        return $this->twig->render('@UmanitEasyAdminTreeBundle/categorized-crud/sidebar.html.twig', [
            'categories'              => $categories,
            'url'                     => $url,
            'category_url_param_name' => $crudControllerFqcn::CATEGORY_URL_PARAM_NAME,
            'current_category'        => $currentCategoryId,
        ]);
    }
}