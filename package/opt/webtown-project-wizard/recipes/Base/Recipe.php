<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\Base;

use AppBundle\Configuration\HiddenRecipe;

/**
 * Class Recipe
 *
 * The BASE.
 *
 * @package Recipes\Base
 */
class Recipe extends HiddenRecipe
{
    const NAME = 'base';

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param string $targetPath
     * @param array  $recipeConfig Here it is the `$globalConfig`
     * @param array  $globalConfig
     *
     * @return array
     *
     * @see \AppBundle\Configuration\Builder::build()
     */
    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        return array_merge(['config' => $recipeConfig], $recipeConfig);
    }
}
