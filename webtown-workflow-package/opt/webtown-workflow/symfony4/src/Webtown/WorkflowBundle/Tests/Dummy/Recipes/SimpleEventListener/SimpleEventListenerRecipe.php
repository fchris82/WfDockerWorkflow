<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 11:23
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleEventListener;


use App\Webtown\WorkflowBundle\Event\RegisterEventListenersInterface;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SimpleEventListenerRecipe extends BaseRecipe implements RegisterEventListenersInterface
{
    /**
     * @var array|string[]
     */
    protected $files = [];

    public function getName()
    {
        return 'simple_event_listener';
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, [$this, 'collectFiles']);
    }

    public function collectFiles(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        $this->files[] = $skeletonFile->getRelativePathname();
    }

    public function getFiles()
    {
        return $this->files;
    }
}
