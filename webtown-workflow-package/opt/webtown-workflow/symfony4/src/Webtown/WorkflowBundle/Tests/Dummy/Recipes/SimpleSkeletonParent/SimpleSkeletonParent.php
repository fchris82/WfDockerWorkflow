<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 16:35
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkeletonParent;

use App\Webtown\WorkflowBundle\Recipes\AbstractTemplateRecipe;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class SimpleSkeletonParent extends BaseRecipe implements AbstractTemplateRecipe
{
    public function getName()
    {
        return 'simple_parent';
    }
}
