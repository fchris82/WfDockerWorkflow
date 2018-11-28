<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\Base;

use Recipes\HiddenRecipe;

/**
 * Class Recipe
 *
 * The BASE.
 */
class BaseRecipe extends HiddenRecipe
{
    const NAME = 'base';

    public function getName()
    {
        return static::NAME;
    }
}
