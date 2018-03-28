<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:23
 */

namespace AppBundle\Event;

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
     * @see DumpEvent
     */
    const BEFORE_DUMP = 'app.configuration.event.before_dump_skeleton';
}
