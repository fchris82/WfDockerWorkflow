<?php declare(strict_types=1);
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
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($namespace, array $skeletonVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->skeletonVars = $skeletonVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonVars(): array
    {
        return $this->skeletonVars;
    }

    /**
     * @param array $skeletonVars
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonVars(array $skeletonVars)
    {
        $this->skeletonVars = $skeletonVars;

        return $this;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getBuildConfig(): array
    {
        return $this->buildConfig;
    }

    /**
     * @param array $buildConfig
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setBuildConfig(array $buildConfig)
    {
        $this->buildConfig = $buildConfig;

        return $this;
    }

    /**
     * @param SkeletonTwigFileInfo $fileInfo
     *
     * @codeCoverageIgnore Simple setter
     */
    public function addSkeletonFileInfo(SkeletonTwigFileInfo $fileInfo)
    {
        $this->skeletonFileInfos[] = $fileInfo;
    }

    /**
     * @return array|SkeletonTwigFileInfo[]
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonFileInfos()
    {
        return $this->skeletonFileInfos;
    }

    /**
     * @param array|SkeletonTwigFileInfo[] $skeletonFileInfos
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonFileInfos($skeletonFileInfos)
    {
        $this->skeletonFileInfos = $skeletonFileInfos;

        return $this;
    }
}
