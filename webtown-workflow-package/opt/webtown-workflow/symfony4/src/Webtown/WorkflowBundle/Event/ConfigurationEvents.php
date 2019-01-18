<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:23
 */

namespace App\Webtown\WorkflowBundle\Event;

use App\Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use App\Webtown\WorkflowBundle\Event\Configuration\RegisterEvent;
use App\Webtown\WorkflowBundle\Event\Configuration\VerboseInfoEvent;

class ConfigurationEvents
{
    /**
     * @see BuildInitEvent
     */
    const BUILD_INIT = 'app.configuration.event.build_init';

    /**
     * @see RegisterEvent
     */
    const REGISTER_EVENT_PREBUILD = 'app.configuration.event.register.prebuild';
    const REGISTER_EVENT_POSTBUILD = 'app.configuration.event.register.postbuild';

    /**
     * @see VerboseInfoEvent
     */
    const VERBOSE_INFO = 'app.configuration.event.verbose_info';
}
