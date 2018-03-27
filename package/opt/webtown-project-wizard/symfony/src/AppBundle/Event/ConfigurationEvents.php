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
     * @see VerboseInfoEvent
     */
    const VERBOSE_INFO = 'app.configuration.event.verbose_info';

    const BEFORE_DUMP = 'app.configuration.event.before_dump_skeleton';
}
