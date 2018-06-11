<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:24
 */

namespace Recipes\Symfony4;

use Recipes\Symfony\AbstractRecipe as BaseRecipe;

/**
 * Class Recipe
 *
 * Symfony 3 friendly environment
 *
 * @package Recipes\Symfony3
 */
class Recipe extends BaseRecipe
{
    const NAME = 'symfony4';
    const SF_CONSOLE_COMMAND = 'bin/console';
    const SF_BIN_DIR = 'vendor/bin';

    public static function getSkeletonParents()
    {
        return [BaseRecipe::class];
    }
}
