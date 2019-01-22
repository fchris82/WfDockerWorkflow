<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace App\Webtown\WfBaseSystemRecipesBundle\SystemRecipes\Base;

use App\Webtown\WorkflowBundle\Recipes\SystemRecipe;

/**
 * Class Recipe
 *
 * The BASE.
 */
class BaseRecipe extends SystemRecipe
{
    const NAME = 'base';

    public function getName()
    {
        return static::NAME;
    }
}