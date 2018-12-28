<?php

namespace App\Webtown\WorkflowBundle\DependencyInjection\Compiler;

use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use App\Webtown\WorkflowBundle\Recipes\AbstractTemplateRecipe;
use App\Webtown\WorkflowBundle\WebtownWorkflowBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class CollectRecipesPass extends AbstractTwigSkeletonPass
{
    /**
     * Register all Recipe service.
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
            $serviceDefinition = $container->getDefinition($serviceId);
            if (!$this->isTheServiceAbstract($serviceDefinition)) {
                $definition->addMethodCall('addRecipe', [new Reference($serviceId)]);
            }

            $this->registerSkeletonService(
                $container->getParameter('twig.default_path'),
                $serviceDefinition,
                $twigFilesystemLoaderDefinition
            );
        }
    }

    protected function isTheServiceAbstract(Definition $serviceDefinition)
    {
        $refClass = new \ReflectionClass($serviceDefinition->getClass());

        return \in_array(AbstractTemplateRecipe::class, $refClass->getInterfaceNames())
            && !\in_array(AbstractTemplateRecipe::class, $refClass->getParentClass()->getInterfaceNames());
    }
}
