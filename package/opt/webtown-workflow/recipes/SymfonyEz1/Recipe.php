<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.29.
 * Time: 15:23
 */

namespace Recipes\SymfonyEz1;

use Recipes\Symfony3\Recipe as BaseRecipe;

class Recipe extends BaseRecipe
{
    const NAME = 'symfony_ez1';
    const SF_CONSOLE_COMMAND = 'app/console';
    const SF_BIN_DIR = 'bin';

    public static function getSkeletonParents()
    {
        return [BaseRecipe::class];
    }
}
