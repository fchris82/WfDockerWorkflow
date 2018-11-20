<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;
use Twig\Loader\FilesystemLoader;

class TwigExtendingPass implements CompilerPassInterface
{
    const WIZARD_TWIG_NAMESPACE = 'wizard';
    const RECIPE_TWIG_NAMESPACE = 'recipe';

    public function process(ContainerBuilder $container)
    {
        $wizardPath = $container->getParameter('wizard_base_dir');
        $recipePath = $container->getParameter('recipe_base_dir');

        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$wizardPath, self::WIZARD_TWIG_NAMESPACE]);
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$recipePath, self::RECIPE_TWIG_NAMESPACE]);
    }
}
