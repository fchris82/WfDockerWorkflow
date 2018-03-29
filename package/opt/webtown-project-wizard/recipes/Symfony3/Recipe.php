<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:24
 */

namespace Recipes\Symfony3;

use AppBundle\Configuration\BaseRecipe;

/**
 * Class Recipe
 *
 * Symfony 3 friendly environment
 *
 * @package Recipes\Symfony3
 */
class Recipe extends BaseRecipe
{
    const NAME = 'symfony3';

    public function getName()
    {
        return self::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>Symfony 3 recipe</comment>')
            ->children()
                ->scalarNode('version')
                    ->info('<comment>Docker image tag. If you want to change image too, use the <info>image</info> option.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('php7.1')
                ->end()
                ->scalarNode('env')
                    ->info('<comment>Symfony environment.</comment>')
                    ->example('dev')
                    ->cannotBeEmpty()
                    ->defaultValue('prod')
                ->end()
                ->scalarNode('host')
                    ->info('<comment>Only for nginx.</comment>')
                    ->setDeprecated('I don\'t know, is it necessary?')
                    ->cannotBeEmpty()
                    ->defaultValue('localhost')
                ->end()
                ->booleanNode('xdebug')
                    ->defaultFalse()
                    ->info('<comment>You can switch on and off the xdebug.</comment>')
                ->end()
                ->scalarNode('xdebug_ide_server_name')
                    ->cannotBeEmpty()
                    ->defaultValue('Docker')
                ->end()
                ->booleanNode('nginx_debug')
                    ->defaultFalse()
                    ->info('<comment>You can switch on and off debug mode. IMPORTANT! The debug mode makes lot of logs!</comment>')
                ->end()
                ->arrayNode('http_auth')
                    ->addDefaultsIfNotSet()
                    ->info('<comment>You can generate a user-password string here: http://www.htaccesstools.com/htpasswd-generator/ ( --> <info>htpasswd</info>).</comment>')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('title')
                            ->defaultValue('Private zone')
                        ->end()
                        ->scalarNode('htpasswd')
                            ->cannotBeEmpty()
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('user_ssh_path')
                    ->info('<comment>Here you can change the .ssh files source</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('~/.ssh')
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
