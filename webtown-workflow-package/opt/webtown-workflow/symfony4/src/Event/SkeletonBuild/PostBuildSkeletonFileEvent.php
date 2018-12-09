<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:33
 */

namespace App\Event\SkeletonBuild;

use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Finder\SplFileInfo;

class PostBuildSkeletonFileEvent extends PreBuildSkeletonFileEvent
{
    /**
     * PostBuildSkeletonFileEvent constructor.
     *
     * @param string|object $namespace
     * @param SkeletonFile  $skeletonFile
     * @param SplFileInfo   $sourceFileInfo
     * @param array         $skeletonVars
     * @param array         $buildConfig
     */
    public function __construct($namespace, SkeletonFile $skeletonFile, SplFileInfo $sourceFileInfo, array $skeletonVars, array $buildConfig)
    {
        $this->skeletonFile = $skeletonFile;

        parent::__construct($namespace, $sourceFileInfo, $skeletonVars, $buildConfig);
    }
}
