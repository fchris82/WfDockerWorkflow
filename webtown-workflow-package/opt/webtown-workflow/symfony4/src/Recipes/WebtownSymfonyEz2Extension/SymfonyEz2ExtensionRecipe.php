<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.29.
 * Time: 15:23
 */

namespace App\Recipes\WebtownSymfonyEz2Extension;

use App\Recipes\Symfony3\Symfony3Recipe;
use Symfony\Component\Config\Definition\Builder\ScalarNodeDefinition;

class SymfonyEz2ExtensionRecipe extends Symfony3Recipe
{
    const NAME = 'symfony_ez2_extension';
    const DEFAULT_VERSION = 'ez2';

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $definitions = $rootNode->getChildNodeDefinitions();
        /** @var ScalarNodeDefinition $projectDirDefinition */
        $projectDirDefinition = $definitions['project_dir'];
        $projectDirDefinition->defaultValue('ez');

        return $rootNode;
    }
}
