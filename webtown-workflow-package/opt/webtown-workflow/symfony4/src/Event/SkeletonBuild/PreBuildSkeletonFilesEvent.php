<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 13:02
 */

namespace App\Event\SkeletonBuild;

use App\Event\NamespacedEvent;
use Symfony\Component\Finder\SplFileInfo;

class PreBuildSkeletonFilesEvent extends NamespacedEvent
{
    /**
     * @var array
     */
    protected $templateVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * @var array|SplFileInfo[]
     */
    protected $skeletonFileInfos;

    /**
     * PreBuildSkeletonFilesEvent constructor.
     * @param array $templateVars
     * @param array $buildConfig
     */
    public function __construct($namespace, array $templateVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->templateVars = $templateVars;
        $this->buildConfig = $buildConfig;
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

    public function addSkeletonFileInfo(SplFileInfo $fileInfo)
    {
        $this->skeletonFileInfos[] = $fileInfo;
    }

    /**
     * @return array|SplFileInfo[]
     */
    public function getSkeletonFileInfos()
    {
        return $this->skeletonFileInfos;
    }

    /**
     * @param array|SplFileInfo[] $skeletonFileInfos
     *
     * @return $this
     */
    public function setSkeletonFileInfos($skeletonFileInfos)
    {
        $this->skeletonFileInfos = $skeletonFileInfos;

        return $this;
    }
}
