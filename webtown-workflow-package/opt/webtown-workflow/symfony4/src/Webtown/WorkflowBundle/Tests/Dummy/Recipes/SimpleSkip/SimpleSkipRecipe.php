<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkip;

use App\Webtown\WorkflowBundle\Exception\SkipRecipeException;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class SimpleSkipRecipe extends BaseRecipe
{
    public function getName()
    {
        return 'simple_skip';
    }

    public function getSkeletonVars($projectPath, $recipeConfig, $globalConfig)
    {
        throw new SkipRecipeException();
    }
}
