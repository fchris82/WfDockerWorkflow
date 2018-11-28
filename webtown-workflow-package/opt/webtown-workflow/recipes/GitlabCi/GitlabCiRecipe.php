<?php

namespace Recipes\GitlabCi;

use Recipes\BaseRecipe;

class GitlabCiRecipe extends BaseRecipe
{
    const NAME = 'gitlab_ci';

    public function getName()
    {
        return static::NAME;
    }
}
