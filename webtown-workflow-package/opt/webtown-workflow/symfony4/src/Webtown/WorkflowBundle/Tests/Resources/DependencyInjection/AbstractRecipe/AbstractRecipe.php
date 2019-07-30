<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:08
 */

namespace App\Webtown\WorkflowBundle\Tests\Resources\DependencyInjection\AbstractRecipe;

use App\Webtown\WorkflowBundle\Recipes\AbstractTemplateRecipe;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class AbstractRecipe extends BaseRecipe implements AbstractTemplateRecipe
{
    public function getName()
    {
        return 'Abstract recipe';
    }
}
