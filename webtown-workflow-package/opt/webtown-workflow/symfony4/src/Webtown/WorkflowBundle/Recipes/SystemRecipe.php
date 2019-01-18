<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace App\Webtown\WorkflowBundle\Recipes;

use App\Webtown\WorkflowBundle\Event\Configuration\RegisterEvent;

abstract class SystemRecipe extends HiddenRecipe
{
    public function onAppConfigurationEventRegisterPrebuild(RegisterEvent $event)
    {
        $event->addRecipe($this);
    }

    public function onAppConfigurationEventRegisterPostbuild(RegisterEvent $event)
    {
        $event->addRecipe($this);
    }
}
