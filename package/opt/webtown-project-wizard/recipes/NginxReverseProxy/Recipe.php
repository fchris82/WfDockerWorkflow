<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\NginxReverseProxy;

use AppBundle\Configuration\BaseRecipe;

/**
 * Class Recipe
 *
 * Allow nginx-reverse-proxy config.
 *
 * @package Recipes\NginxReverseProxy
 */
class Recipe extends BaseRecipe
{
    const NAME = 'nginx_reverse_proxy';

    public function getName()
    {
        return self::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>You can enable the nginx-reverse-proxy.</comment>')
            ->children()
                ->scalarNode('network_name')
                    ->info('<comment>The nginx-reverse-proxy network name.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('reverse-proxy')
                ->end()
                ->arrayNode('settings')
                    ->info('<comment>You have to set the service and its <info>host</info> and <info>port</info> settings.</comment>')
                    ->useAttributeAsKey('service')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('host')
                                ->example('project.loc')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('port')
                                ->cannotBeEmpty()
                                ->defaultValue(80)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
