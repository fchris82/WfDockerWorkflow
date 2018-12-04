<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.04.
 * Time: 9:40
 */

namespace App\DependencyInjection\Compiler;

use App\Extension\ExtensionManager;
use App\Extension\Installer\InstallerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CollectExtensionInstallersPass
 *
 * We register all available extension installer class. You have to use the `app.extension.installer` tag name.
 *
 * | Parameter | Description                                                                                         |
 * | --------- | --------------------------------------------------------------------------------------------------- |
 * | priority  | You can handle the installer order. It is important at listing only. The highest value will be      |
 * |           | above. It isn't required! If you don't set it, the program will use the static `getPriority()`      |
 * |           | function.                                                                                           |
 *
 * <code>
 *     App\Extension\Installer\CustomInstaller1:
 *         tags:
 *             - { name: app.extension.installer }
 *
 *     # Override the `getPriority()` value
 *     App\Extension\Installer\CustomInstaller2
 *         tags:
 *             - { name: app.extension.installer, priority: 80 }
 * </code>
 */
class CollectExtensionInstallersPass implements CompilerPassInterface
{
    const TAG_NAME = 'app.extension.installer';

    /**
     * You can modify the container here before it is dumped to PHP code.
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(ExtensionManager::class);

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $taggedService) {
            // Amelyik service igényli, ott bejegyezzük a container-t
            $serviceDef = $container->getDefinition($serviceId);
            /** @var InstallerInterface $className */
            $className = $serviceDef->getClass();

            foreach ($taggedService as $attributes) {
                $priority = (array_key_exists('priority', $attributes))
                    ? $attributes['priority']
                    : $className::getPriority();

                $definition->addMethodCall('addInstaller', [new Reference($serviceId), $priority]);
            }
        }
    }
}
