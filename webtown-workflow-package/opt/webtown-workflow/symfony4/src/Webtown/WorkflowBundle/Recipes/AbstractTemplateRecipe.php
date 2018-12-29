<?php

namespace App\Webtown\WorkflowBundle\Recipes;

use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\CollectRecipesPass;

/**
 * Interface AbstractTemplateRecipe
 *
 * There are abstract recipes, like AbstractSymfonyRecipe. We want to use them skeletons, so we need to register it. But
 * if we use "abstract" class, the CollectRecipesPass won't get it like a service, and we won't be able to reach the
 * skeleton files.
 * The solution: use this interface to sign these abstract recipes.
 *
 * @see CollectRecipesPass::isTheServiceAbstract()
 */
interface AbstractTemplateRecipe
{
}