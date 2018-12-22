<?php

namespace App\Webtown\WorkflowBundle\DependencyInjection\Compiler;

use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use App\Webtown\WorkflowBundle\WebtownWorkflowBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class CollectRecipesPass extends AbstractTwigSkeletonPass
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws \ReflectionException
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(RecipeManager::class);
        $twigFilesystemLoaderDefinition = $container->getDefinition(parent::DEFAULT_TWIG_LOADER);

        foreach ($container->findTaggedServiceIds(WebtownWorkflowBundle::RECIPE_TAG) as $serviceId => $taggedService) {
            $definition->addMethodCall('addRecipe', [new Reference($serviceId)]);

            $serviceDefinition = $container->getDefinition($serviceId);
            $this->registerSkeletonService(
                $container->getParameter('twig.default_path'),
                $serviceDefinition,
                $twigFilesystemLoaderDefinition
            );
        }
    }
}
