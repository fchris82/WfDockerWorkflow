<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 12:52
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Simple;

use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class SimpleRecipe extends BaseRecipe
{
    public function getName()
    {
        return 'simple';
    }
}
