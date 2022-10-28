<?php

namespace Umanit\EasyAdminTreeBundle\Factory;

use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Contracts\Translation\TranslatableInterface;
use Umanit\EasyAdminTreeBundle\Config\Menu\TreeMenuItem;

class TreeMenuFactory
{
    protected AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    public function create(TranslatableInterface|string $label, ?string $icon, string $entityFqcn): TreeMenuItem
    {
        $menuItem = new TreeMenuItem($label, $icon, $entityFqcn);

        $this->adminUrlGenerator
            // remove all existing query params to avoid keeping search queries, filters and pagination
            ->unsetAll()
            // add the index and subIndex query parameters to display the selected menu item
            ->set(EA::MENU_INDEX, $index)->set(EA::SUBMENU_INDEX, $subIndex)
            // set any other parameters defined by the menu item
            ->setAll($routeParameters);

        $menuItem->getAsDto()->setLinkUrl('https://google.fr');

        return $menuItem;
    }
}
