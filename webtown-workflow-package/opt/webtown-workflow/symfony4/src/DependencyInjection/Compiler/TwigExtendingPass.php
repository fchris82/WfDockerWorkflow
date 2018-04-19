<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Twig\Loader\FilesystemLoader;

class TwigExtendingPass implements CompilerPassInterface
{
    const SKELETON_TWIG_NAMESPACE = 'skeleton';
    const RECIPE_TWIG_NAMESPACE = 'recipe';

    public function process(ContainerBuilder $container)
    {
        $skeletonPath = $container->getParameter('skeleton_base_dir');
        $recipePath = $container->getParameter('recipe_base_dir');

        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.native_filesystem');
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$skeletonPath, self::SKELETON_TWIG_NAMESPACE]);
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$recipePath, self::RECIPE_TWIG_NAMESPACE]);
    }
}
