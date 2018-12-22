<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:23
 */

namespace App\Webtown\WorkflowBundle\Event;

class ConfigurationEvents
{
    /**
     * @see BuildInitEvent
     */
    const BUILD_INIT = 'app.configuration.event.build_init';

    /**
     * @see VerboseInfoEvent
     */
    const VERBOSE_INFO = 'app.configuration.event.verbose_info';

    /**
     * @see FinishEvent
     */
    const FINISH = 'app.configuration.event.finish';
}
