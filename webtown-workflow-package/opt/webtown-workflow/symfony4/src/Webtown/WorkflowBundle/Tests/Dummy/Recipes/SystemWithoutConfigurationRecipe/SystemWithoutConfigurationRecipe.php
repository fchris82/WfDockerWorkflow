<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.22.
 * Time: 13:47
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SystemWithoutConfigurationRecipe;


use App\Webtown\WorkflowBundle\Recipes\SystemRecipe;

class SystemWithoutConfigurationRecipe extends SystemRecipe
{
    public function getName()
    {
        return 'system_without_configuration';
    }
}