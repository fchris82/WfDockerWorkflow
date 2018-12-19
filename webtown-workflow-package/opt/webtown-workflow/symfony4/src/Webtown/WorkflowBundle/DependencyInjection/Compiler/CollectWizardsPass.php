<?php

namespace App\Webtown\WorkflowBundle\DependencyInjection\Compiler;

use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use App\Webtown\WorkflowBundle\Wizard\Manager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class CollectWizardsPass implements CompilerPassInterface
{
    const TAG_NAME = 'wf.wizard';

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(Manager::class);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $taggedService) {
            $definition->addMethodCall('addWizard', [new Reference($serviceId)]);
        }
    }
}
