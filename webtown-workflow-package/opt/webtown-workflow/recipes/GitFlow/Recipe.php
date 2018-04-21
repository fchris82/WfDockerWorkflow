<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\GitFlow;

use Recipes\BaseRecipe;

/**
 * Class Recipe
 *
 * Extends the base functions with the gitflow commands.
 *
 * @package Recipes\GitFlow
 */
class Recipe extends BaseRecipe
{
    const NAME = 'git_flow';

    public function getName()
    {
        return static::NAME;
    }


    public function getConfig()
    {
        $rootNode = parent::getConfig();

        /**
         *  git_flow:
         *      develop: dev
         *      feature: f
         *      hotfix:  h
         */
        $rootNode
            ->info('<comment>GitFlow</comment>')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('develop')
                    ->info('<comment>Develop branch name</comment>')
                    ->defaultValue('develop')
                ->end()
                ->scalarNode('feature')
                    ->info('<comment>Feature branches prefix</comment>')
                    ->defaultValue('feature')
                ->end()
                ->scalarNode('hotfix')
                    ->info('<comment>Hotfix branches prefix</comment>')
                    ->defaultValue('hotfix')
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        $vars = parent::getTemplateVars($targetPath, $recipeConfig, $globalConfig);
        $vars['binary_directory'] = __DIR__ . '/bin';

        return $vars;
    }
}
