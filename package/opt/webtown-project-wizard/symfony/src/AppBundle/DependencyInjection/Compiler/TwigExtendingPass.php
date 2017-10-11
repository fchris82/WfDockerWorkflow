<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigExtendingPass implements CompilerPassInterface
{
    const TWIG_NAMESPACE = 'skeleton';

    public function process(ContainerBuilder $container)
    {
        $path = $container->getParameter('skeleton_base_dir');

        $twigFilesystemLoaderDefinition = $container->getDefinition('twig.loader.filesystem');
        $twigFilesystemLoaderDefinition->addMethodCall('addPath', [$path, self::TWIG_NAMESPACE]);
    }
}
