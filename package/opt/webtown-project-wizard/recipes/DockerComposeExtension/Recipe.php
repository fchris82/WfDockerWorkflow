<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 11:31
 */

namespace Recipes\DockerComposeExtension;

use AppBundle\Configuration\HiddenRecipe;
use AppBundle\Exception\SkipRecipeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Recipe
 *
 * You can insert Docker Compose configuration in the project config file:
 * <code>
 *  docker_compose:
 *      extension:
 *          # Here start the 'docker-compose.yml'. The `version` will be automated there, you mustn't use it!
 *          services:
 *              web:
 *                  environment:
 *                      TEST: test
 * <code>
 *
 * @package Recipes\DockerComposeExtension
 */
class Recipe extends HiddenRecipe
{
    const NAME = 'docker_compose_extension';

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param string $targetPath
     * @param array $recipeConfig Here it is the `$globalConfig`
     * @param array $globalConfig
     *
     * @return array
     *
     * @throws SkipRecipeException
     *
     * @see \AppBundle\Configuration\Builder::build()
     */
    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        if (empty($globalConfig['docker_compose']['extension'])) {
            throw new SkipRecipeException();
        }

        $composeConfig = $globalConfig['docker_compose']['extension'];
        $recipeConfig = array_merge(
            $recipeConfig,
            [
                'yaml_dump' => Yaml::dump($composeConfig, 4),
            ]
        );

        return parent::getTemplateVars($targetPath, $recipeConfig, $globalConfig);
    }
}
