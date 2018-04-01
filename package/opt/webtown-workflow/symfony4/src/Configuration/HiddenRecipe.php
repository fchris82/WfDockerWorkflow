<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace App\Configuration;


abstract class HiddenRecipe extends BaseRecipe
{
    public function getConfig()
    {
        throw new \Exception('The hidden recipe doesn\'t have config!');
    }
}
