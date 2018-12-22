<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:26
 */

namespace App\Webtown\WorkflowBundle\Configuration;

use App\Webtown\WorkflowBundle\Exception\MissingRecipeException;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class RecipeManager
{
    /**
     * @var BaseRecipe[]
     */
    protected $recipes = [];

    public function addRecipe(BaseRecipe $recipe)
    {
        if (array_key_exists($recipe->getName(), $this->recipes)) {
            throw new InvalidConfigurationException(sprintf(
                'The `%s` recipe has been already existed! [`%s` vs `%s`]',
                $recipe->getName(),
                \get_class($this->recipes[$recipe->getName()]),
                \get_class($recipe)
            ));
        }
        $this->recipes[$recipe->getName()] = $recipe;
    }

    /**
     * @return BaseRecipe[]
     */
    public function getRecipes()
    {
        return $this->recipes;
    }

    /**
     * @param string $recipeName
     *
     * @throws MissingRecipeException
     *
     * @return BaseRecipe
     */
    public function getRecipe($recipeName)
    {
        $recipes = $this->getRecipes();
        if (!array_key_exists($recipeName, $recipes)) {
            throw new MissingRecipeException(sprintf('The `%s` recipe is missing!', $recipeName));
        }

        return $recipes[$recipeName];
    }
}
