<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 13:02
 */

namespace App\Webtown\WorkflowBundle\Event\SkeletonBuild;

use App\Webtown\WorkflowBundle\Event\NamespacedEvent;
use App\Webtown\WorkflowBundle\Skeleton\SkeletonTwigFileInfo;

class PreBuildSkeletonFilesEvent extends NamespacedEvent
{
    /**
     * @var array
     */
    protected $skeletonVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * @var array|SkeletonTwigFileInfo[]
     */
    protected $skeletonFileInfos;

    /**
     * PreBuildSkeletonFilesEvent constructor.
     *
     * @param array $skeletonVars
     * @param array $buildConfig
     */
    public function __construct($namespace, array $skeletonVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->skeletonVars = $skeletonVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return array
     */
    public function getSkeletonVars(): array
    {
        return $this->skeletonVars;
    }

    /**
     * @param array $skeletonVars
     *
     * @return $this
     */
    public function setSkeletonVars(array $skeletonVars)
    {
        $this->skeletonVars = $skeletonVars;

        return $this;
    }

    /**
     * @return array
     */
    public function getBuildConfig(): array
    {
        return $this->buildConfig;
    }

    /**
     * @param array $buildConfig
     *
     * @return $this
     */
    public function setBuildConfig(array $buildConfig)
    {
        $this->buildConfig = $buildConfig;

        return $this;
    }

    public function addSkeletonFileInfo(SkeletonTwigFileInfo $fileInfo)
    {
        $this->skeletonFileInfos[] = $fileInfo;
    }

    /**
     * @return array|SkeletonTwigFileInfo[]
     */
    public function getSkeletonFileInfos()
    {
        return $this->skeletonFileInfos;
    }

    /**
     * @param array|SkeletonTwigFileInfo[] $skeletonFileInfos
     *
     * @return $this
     */
    public function setSkeletonFileInfos($skeletonFileInfos)
    {
        $this->skeletonFileInfos = $skeletonFileInfos;

        return $this;
    }
}
