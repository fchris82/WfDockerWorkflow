<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\GitFlow;

use AppBundle\Configuration\BaseRecipe;

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
        return self::NAME;
    }

    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        $vars = parent::getTemplateVars($targetPath, $recipeConfig, $globalConfig);
        $vars['binary_directory'] = __DIR__ . '/bin';

        return $vars;
    }
}
