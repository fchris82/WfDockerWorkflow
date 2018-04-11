<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:24
 */

namespace Recipes\Symfony2;

use Recipes\Symfony3\Recipe as BaseRecipe;

/**
 * Class Recipe
 *
 * Symfony 2 friendly environment
 *
 * @package Recipes\Symfony2
 */
class Recipe extends BaseRecipe
{
    const NAME = 'symfony2';
    const SF_CONSOLE_COMMAND = 'app/console';
    const SF_BIN_DIR = 'bin';

    public static function getSkeletonParents()
    {
        return [BaseRecipe::class];
    }
}
