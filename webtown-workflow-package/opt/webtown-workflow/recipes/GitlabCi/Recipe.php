<?php

namespace Recipes\GitlabCi;

use Recipes\BaseRecipe;

class Recipe extends BaseRecipe
{
    const NAME = 'gitlab_ci';

    public function getName()
    {
        return static::NAME;
    }
}