<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 11:26
 */

namespace App\Webtown\WorkflowBundle\Event;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface RegisterEventListenersInterface
 *
 * We want to register some recipes, but only that what we are using. So the EventSubscriberInterface isn't good for us
 * for this situations. The solution is this interface.
 */
interface RegisterEventListenersInterface
{
    public function registerEventListeners(EventDispatcherInterface $eventDispatcher);
}
