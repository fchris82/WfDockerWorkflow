<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:26
 */

namespace AppBundle\Configuration;

use AppBundle\Exception\MissingRecipeException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class RecipeManager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $recipesPath;

    /**
     * @var BaseRecipe[]
     */
    protected $recipes;

    /**
     * RecipeManager constructor.
     * @param string $recipesPath
     */
    public function __construct($recipesPath)
    {
        $this->recipesPath = $recipesPath;
    }

    /**
     * @return BaseRecipe[]
     */
    public function getRecipes()
    {
        if (!$this->recipes) {
            $finder = new Finder();
            // @todo (Chris) Meg kellene oldani, hogy be lehessen húzni saját megoldásokat!
            $finder->in($this->recipesPath)->name('*.php');
            $this->recipes = [];
            /** @var SplFileInfo $recipeFile */
            foreach ($finder as $recipeFile) {
                $fullClass = sprintf(
                    'Recipes\\%s\\Recipe',
                    str_replace('/', '\\', $recipeFile->getRelativePath())
                );
                /** @var BaseRecipe $recipe */
                $recipe = $this->container->get($fullClass);
                if (array_key_exists($recipe->getName(), $this->recipes)) {
                    throw new InvalidConfigurationException(sprintf(
                        'The `%s` recipe has been already existed! [`%s` vs `%s`]',
                        $recipe->getName(),
                        get_class($this->recipes[$recipe->getName()]),
                        get_class($recipe)
                    ));
                }
                $this->recipes[$recipe->getName()] = $recipe;
            }
        }

        return $this->recipes;
    }

    /**
     * @param string $recipeName
     *
     * @return BaseRecipe
     *
     * @throws MissingRecipeException
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
