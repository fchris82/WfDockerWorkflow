<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace App\Webtown\WorkflowBundle\Recipes;

abstract class HiddenRecipe extends BaseRecipe
{
    public function getConfig()
    {
        throw new \Exception('The hidden recipes don\'t have and don\'t need config!');
    }
}
