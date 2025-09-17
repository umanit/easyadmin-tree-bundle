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

        return $this->twig->render('@UmanitEasyAdminTreeBundle/categorized-crud/sidebar.html.twig', [
            'categories' => $categories,
            'url' => $this->getCategoryListUrl($crudControllerFqcn),
            'category_url_param_name' => $crudControllerFqcn::CATEGORY_URL_PARAM_NAME,
            'current_category' => $currentCategoryId,
        ]);
    }

    private function getCategoryListUrl(string $crudControllerFqcn): string
    {
        $url = $this->urlGenerator
            ->setController($crudControllerFqcn)
            ->setAction(Action::INDEX)
            ->generateUrl()
        ;
        $parsed = parse_url($url);
        $query = $parsed['query'];
        parse_str($query, $params);
        unset($params['umanit_category']);
        $string = http_build_query($params);
        $url = strtok($url, '?');

        if ('' !== $string) {
            $url .= '?' . $string;
        }

        return $url;
    }
}