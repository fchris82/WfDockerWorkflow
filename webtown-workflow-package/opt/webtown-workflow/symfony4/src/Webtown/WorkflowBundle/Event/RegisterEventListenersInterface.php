<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 11:26
 */

namespace App\Webtown\WorkflowBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface RegisterEventListenersInterface
{
    public function registerEventListeners(EventDispatcherInterface $eventDispatcher);
}
