<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 10:57
 */

namespace App\Webtown\WorkflowBundle\Tests\Resources\DependencyInjection\OverriddenRecipe;


use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class OverriddenSkeletonsRecipe extends BaseRecipe
{
    public function getName()
    {
        return 'overridden';
    }
}
