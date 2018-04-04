<?php

namespace App\DependencyInjection\Compiler;

use App\Wizard\Manager;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CollectWizardsPass.
 *
 * Itt keressük meg az összes `wizard` tag-gel ellátott service-t.
 *
 * | Paraméter név | Leírás                                                                                         |
 * | ------------- | ---------------------------------------------------------------------------------------------- |
 * | group         | KÖTELEZŐ! A csoport neve, ahova szeretnénk bedrótozni az adott wizard-ot.                      |
 * | priority      | A sorrendiséget lehet szabályozni vele. Minél nagyobb az érték, annál előrébb fog megjelnni az |
 * |               | az adott wizard a csoportban, illetve a csoportok sorrendjére is hatással van.!                |
 *
 * <code>
 *     App\Wizard\Symfony\SymfonyProjectChainWizard:
 *         tags:
 *             - { name: wizard, group: 'Full builder wizards', priority: 100 }
 *
 *     App\Wizard\Symfony\SymfonyBuildWizard:
 *         tags:
 *             - { name: wizard, group: 'Only builders', priority: 50 }
 *
 *     App\Wizard\Base\GitlabCISkeleton:
 *         tags:
 *             - { name: wizard, group: 'Decorators' }
 * </code>
 */
class CollectWizardsPass implements CompilerPassInterface
{
    const TAG_NAME = 'wizard';

    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(Manager::class);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $taggedService) {
            // Amelyik service igényli, ott bejegyezzük a container-t
            $serviceDef = $container->getDefinition($serviceId);
            $reflClass = new \ReflectionClass($serviceDef->getClass());
            if ($reflClass->implementsInterface(ContainerAwareInterface::class)) {
                $serviceDef->addMethodCall('setContainer', [new Reference('service_container')]);
            }

            foreach ($taggedService as $attributes) {
                $priority = (array_key_exists('priority', $attributes))
                    ? $attributes['priority']
                    : 0;
                if (!array_key_exists('group', $attributes)) {
                    throw new InvalidConfigurationException(sprintf(
                        'Missiong `group` attribute in `%s` tag at the `%s` service.',
                        self::TAG_NAME,
                        $serviceId
                    ));
                }
                $group = $attributes['group'];

                $definition->addMethodCall('addWizard', [new Reference($serviceId), $group, $priority]);
            }
        }
    }
}
