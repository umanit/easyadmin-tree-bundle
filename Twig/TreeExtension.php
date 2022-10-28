<?php

namespace Umanit\EasyAdminTreeBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TreeExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('categorization_sidebar', [TreeRuntime::class, 'renderCategorizationSidebar']),
        ];
    }
}