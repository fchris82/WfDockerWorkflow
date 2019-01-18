<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 17:18
 */

namespace App\Webtown\WorkflowBundle\Tests\Configuration;

use App\Webtown\WorkflowBundle\Configuration\Builder;
use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use App\Webtown\WorkflowBundle\Tests\Dummy\Filesystem;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BuilderTest extends TestCase
{
    public function testBuild(array $recipes)
    {
        $filesystem = new Filesystem('');
        $recipeManager = new RecipeManager();
        foreach ($recipes as $recipe) {
            $recipeManager->addRecipe($recipe);
        }
        $builder = new Builder($filesystem, $recipeManager, new EventDispatcher());
    }
}
