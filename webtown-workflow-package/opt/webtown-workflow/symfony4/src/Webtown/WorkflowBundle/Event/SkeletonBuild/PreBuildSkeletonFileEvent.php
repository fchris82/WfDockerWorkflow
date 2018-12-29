<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:22
 */

namespace App\Webtown\WorkflowBundle\Event\SkeletonBuild;

use App\Webtown\WorkflowBundle\Event\NamespacedEvent;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\SkeletonTwigFileInfo;

class PreBuildSkeletonFileEvent extends NamespacedEvent
{
    /**
     * @var SkeletonTwigFileInfo
     */
    protected $sourceFileInfo;

    /**
     * @var array
     */
    protected $skeletonVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * @var SkeletonFile
     */
    protected $skeletonFile;

    /**
     * PreBuildSkeletonFileEvent constructor.
     *
     * @param string|object        $namespace
     * @param SkeletonTwigFileInfo $sourceFileInfo
     * @param array                $skeletonVars
     * @param array                $buildConfig
     */
    public function __construct($namespace, SkeletonTwigFileInfo $sourceFileInfo, array $skeletonVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->sourceFileInfo = $sourceFileInfo;
        $this->skeletonVars = $skeletonVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return SkeletonTwigFileInfo
     */
    public function getSourceFileInfo(): SkeletonTwigFileInfo
    {
        return $this->sourceFileInfo;
    }

    /**
     * @param SkeletonTwigFileInfo $sourceFileInfo
     *
     * @return $this
     */
    public function setSourceFileInfo(SkeletonTwigFileInfo $sourceFileInfo)
    {
        $this->sourceFileInfo = $sourceFileInfo;

        return $this;
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

    public function getSkeletonVar(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->skeletonVars)) {
            return $default;
        }

        return $this->skeletonVars[$key];
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

    /**
     * @return SkeletonFile|null
     */
    public function getSkeletonFile(): ?SkeletonFile
    {
        return $this->skeletonFile;
    }

    /**
     * @param SkeletonFile $skeletonFile
     *
     * @return $this
     */
    public function setSkeletonFile(SkeletonFile $skeletonFile)
    {
        $this->skeletonFile = $skeletonFile;

        return $this;
    }
}
