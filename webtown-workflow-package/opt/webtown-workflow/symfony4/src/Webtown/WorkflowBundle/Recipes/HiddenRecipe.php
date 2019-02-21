<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace App\Webtown\WorkflowBundle\Recipes;

use App\Webtown\WorkflowBundle\Exception\RecipeHasNotConfigurationException;

abstract class HiddenRecipe extends BaseRecipe
{
    /**
     * @deprecated We don't call it if the recipe is based on HiddenRecipe
     */
    public function getConfig()
    {
        throw new RecipeHasNotConfigurationException('The hidden recipes don\'t have and don\'t need config!');
    }
}
