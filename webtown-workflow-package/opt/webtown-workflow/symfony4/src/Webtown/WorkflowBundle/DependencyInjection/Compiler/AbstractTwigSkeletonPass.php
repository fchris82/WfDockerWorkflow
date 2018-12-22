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

    /**
     * @param string $twigDefaultPath
     * @param Definition $serviceDefinition
     * @param Definition $twigLoaderDefinition
     * @throws \ReflectionException
     */
    protected function registerSkeletonService(string $twigDefaultPath, Definition $serviceDefinition, Definition $twigLoaderDefinition)
    {
        $refClass = new \ReflectionClass($serviceDefinition->getClass());

        $namespace = SkeletonHelper::generateTwigNamespace($refClass);

        // Override
        $overridePath = $twigDefaultPath . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . $namespace;
        if (is_dir($overridePath)) {
            $twigLoaderDefinition->addMethodCall('addPath', [$overridePath, $namespace]);
        }

        $path = dirname($refClass->getFileName());
        $twigLoaderDefinition->addMethodCall('addPath', [$path, $namespace]);
    }
}
