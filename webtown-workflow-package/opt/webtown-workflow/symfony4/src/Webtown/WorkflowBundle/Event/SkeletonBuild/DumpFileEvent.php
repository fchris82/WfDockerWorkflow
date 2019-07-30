<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.27.
 * Time: 11:52
 */

namespace App\Webtown\WorkflowBundle\Event\SkeletonBuild;

use App\Webtown\WorkflowBundle\Event\NamespacedEvent;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Filesystem\Filesystem;

class DumpFileEvent extends NamespacedEvent
{
    /**
     * @var SkeletonFile
     */
    protected $skeletonFile;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * DumpFileEvent constructor.
     *
     * @param string|object $namespace
     * @param SkeletonFile  $skeletonFile
     * @param Filesystem    $fileSystem
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($namespace, SkeletonFile $skeletonFile, Filesystem $fileSystem)
    {
        parent::__construct($namespace);
        $this->skeletonFile = $skeletonFile;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return SkeletonFile
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonFile(): SkeletonFile
    {
        return $this->skeletonFile;
    }
}
