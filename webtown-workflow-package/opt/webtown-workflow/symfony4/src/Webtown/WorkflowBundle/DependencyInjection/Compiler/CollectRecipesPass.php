<?php

namespace App\Webtown\WorkflowBundle\DependencyInjection\Compiler;

use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CollectRecipesPass implements CompilerPassInterface
{
    const TAG_NAME = 'wf.recipe';

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(RecipeManager::class);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $taggedService) {
            $definition->addMethodCall('addRecipe', [new Reference($serviceId)]);
        }
    }
}
