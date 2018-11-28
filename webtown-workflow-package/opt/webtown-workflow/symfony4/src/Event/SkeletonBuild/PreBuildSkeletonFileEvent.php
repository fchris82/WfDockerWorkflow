<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.26.
 * Time: 17:22
 */

namespace App\Event\SkeletonBuild;

use App\Event\NamespacedEvent;
use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Finder\SplFileInfo;

class PreBuildSkeletonFileEvent extends NamespacedEvent
{
    /**
     * @var SplFileInfo
     */
    protected $sourceFileInfo;

    /**
     * @var array
     */
    protected $templateVars;

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
     * @param string|object $namespace
     * @param SplFileInfo   $sourceFileInfo
     * @param array         $templateVars
     * @param array         $buildConfig
     */
    public function __construct($namespace, SplFileInfo $sourceFileInfo, array $templateVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->sourceFileInfo = $sourceFileInfo;
        $this->templateVars = $templateVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return SplFileInfo
     */
    public function getSourceFileInfo(): SplFileInfo
    {
        return $this->sourceFileInfo;
    }

    /**
     * @param SplFileInfo $sourceFileInfo
     *
     * @return $this
     */
    public function setSourceFileInfo(SplFileInfo $sourceFileInfo)
    {
        $this->sourceFileInfo = $sourceFileInfo;

        return $this;
    }

    /**
     * @return array
     */
    public function getTemplateVars(): array
    {
        return $this->templateVars;
    }

    /**
     * @param array $templateVars
     *
     * @return $this
     */
    public function setTemplateVars(array $templateVars)
    {
        $this->templateVars = $templateVars;

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
