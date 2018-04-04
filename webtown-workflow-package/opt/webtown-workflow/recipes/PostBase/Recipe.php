<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\PostBase;

use Recipes\HiddenRecipe;

/**
 * Class Recipe
 *
 * After the all
 *
 * @package Recipes\PostBase
 */
class Recipe extends HiddenRecipe
{
    const NAME = 'post_base';

    public function getName()
    {
        return static::NAME;
    }
}
