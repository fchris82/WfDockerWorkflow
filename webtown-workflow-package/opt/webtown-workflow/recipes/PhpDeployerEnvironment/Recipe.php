<?php

namespace Recipes\PhpDeployerEnvironment;

use Recipes\BaseRecipe as BaseRecipe;

class Recipe extends BaseRecipe
{
    const NAME = 'php_deployer_environment';

    public function getName()
    {
        return static::NAME;
    }
    public function getConfig()
    {
        $rootNode = parent::getConfig();

        /**
         *  php_deployer_environment:
         *      share: engine
         */

        $rootNode
            ->info('<comment>PHP Deployer environment</comment>')
            ->children()
                ->arrayNode('share')
                    ->info('<comment>Share deployer share directory. Service name list.</comment>')
                    ->scalarPrototype()->end()
                    ->defaultValue(['engine'])
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
