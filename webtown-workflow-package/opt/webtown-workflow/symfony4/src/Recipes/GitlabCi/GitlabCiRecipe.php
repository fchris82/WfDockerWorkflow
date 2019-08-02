<?php declare(strict_types=1);

namespace App\Recipes\GitlabCi;

use Webtown\WorkflowBundle\Recipes\BaseRecipe;

class GitlabCiRecipe extends BaseRecipe
{
    const NAME = 'gitlab_ci';

    public function getName(): string
    {
        return static::NAME;
    }
}
