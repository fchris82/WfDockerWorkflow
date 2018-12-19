<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 18:25
 */

namespace App\Webtown\WorkflowBundle\Event;

class ExtensionEvents
{
    const PRE_INSTALL_EVENT = 'app.extension.pre_install';
    const POST_INSTALL_EVENT = 'app.extension.post_install';
    const CLEANUP_INSTALL_EVENT = 'app.extension.cleanup_install';
}
