<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.27.
 * Time: 11:25
 */

namespace App\Webtown\WorkflowBundle\Skeleton;

use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use App\Webtown\WorkflowBundle\Exception\SkipSkeletonFileException;
use App\Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonDirectory;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Filesystem\Filesystem;

trait BuilderTrait
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    abstract protected function eventBeforeDumpFile(DumpFileEvent $event);

    abstract protected function eventBeforeDumpTargetExists(DumpFileEvent $event);

    abstract protected function eventAfterDumpFile(DumpFileEvent $event);

    abstract protected function eventSkipDumpFile(DumpFileEvent $event);

    /**
     * @param array|SkeletonFile[] $skeletonFiles
     */
    protected function dumpSkeletonFiles($skeletonFiles)
    {
        foreach ($skeletonFiles as $skeletonFile) {
            $event = new DumpFileEvent($this, $skeletonFile, $this->fileSystem);
            try {
                $this->eventBeforeDumpFile($event);
                $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::BEFORE_DUMP_FILE, $event);

                if ($this->fileSystem->exists($skeletonFile->getFullTargetPathname())) {
                    $this->eventBeforeDumpTargetExists($event);
                    $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::BEFORE_DUMP_TARGET_EXISTS, $event);
                }

                $skeletonFile = $event->getSkeletonFile();
                if ($skeletonFile instanceof SkeletonDirectory) {
                    $this->fileSystem->mkdir($skeletonFile->getFullTargetPathname());
                } elseif (SkeletonFile::HANDLE_EXISTING_APPEND == $skeletonFile->getHandleExisting()) {
                    $this->fileSystem->appendToFile(
                        $skeletonFile->getFullTargetPathname(),
                        $skeletonFile->getContents()
                    );
                } else {
                    $this->fileSystem->dumpFile(
                        $skeletonFile->getFullTargetPathname(),
                        $skeletonFile->getContents()
                    );
                }

                if ($skeletonFile instanceof ExecutableSkeletonFile) {
                    $this->fileSystem->chmod($skeletonFile->getRelativePathname(), $skeletonFile->getPermission());
                }

                $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, $event);
                $this->eventAfterDumpFile($event);
            } catch (SkipSkeletonFileException $e) {
                $this->eventSkipDumpFile($event);
                $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::SKIP_DUMP_FILE, $event);
            }
        }
    }
}
