<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 16:01
 */

namespace App\Webtown\WorkflowBundle\DependencyInjection\Compiler;

use App\Webtown\WorkflowBundle\Skeleton\SkeletonHelper;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Definition;

abstract class AbstractTwigSkeletonPass implements CompilerPassInterface
{
    const DEFAULT_TWIG_LOADER = 'twig.loader.native_filesystem';

    protected function registerSkeletonService(Definition $serviceDefinition, Definition $twigLoaderDefinition)
    {
        $refClass = new \ReflectionClass($serviceDefinition->getClass());

        $namespace = SkeletonHelper::generateTwigNamespace($refClass);
        $path = dirname($refClass->getFileName()) . DIRECTORY_SEPARATOR . SkeletonHelper::DIR;

        if (file_exists($path) && is_dir($path)) {
            $twigLoaderDefinition->addMethodCall('addPath', [$path, $namespace]);
        }
    }
}
