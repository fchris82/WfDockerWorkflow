<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.26.
 * Time: 17:08
 */

namespace App\Webtown\WorkflowBundle\Recipes;

use App\Webtown\WorkflowBundle\Event\Configuration\RegisterEvent;
use App\Webtown\WorkflowBundle\Event\ConfigurationEvents;

abstract class SystemRecipe extends HiddenRecipe
{
    /**
     * @param RegisterEvent $event
     *
     * @see ConfigurationEvents::REGISTER_EVENT_PREBUILD
     */
    public function onAppConfigurationEventRegisterPrebuild(RegisterEvent $event)
    {
        $event->addRecipe($this);
    }

    /**
     * @param RegisterEvent $event
     *
     * @see ConfigurationEvents::REGISTER_EVENT_POSTBUILD
     */
    public function onAppConfigurationEventRegisterPostbuild(RegisterEvent $event)
    {
        $event->addRecipe($this);
    }
}
