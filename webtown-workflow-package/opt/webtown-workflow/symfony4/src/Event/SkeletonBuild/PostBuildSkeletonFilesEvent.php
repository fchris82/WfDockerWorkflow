<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 13:10
 */

namespace App\Event\SkeletonBuild;

use App\Event\NamespacedEvent;
use App\Skeleton\FileType\SkeletonFile;

class PostBuildSkeletonFilesEvent extends NamespacedEvent
{
    /**
     * @var array|SkeletonFile[]
     */
    protected $skeletonFiles;

    /**
     * @var array
     */
    protected $templateVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * PostBuildSkeletonFilesEvent constructor.
     *
     * @param $namespace
     * @param array                $templateVars
     * @param array                $buildConfig
     * @param SkeletonFile[]|array $skeletonFiles
     */
    public function __construct($namespace, $skeletonFiles, array $templateVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->skeletonFiles = $skeletonFiles;
        $this->templateVars = $templateVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return SkeletonFile[]|array
     */
    public function getSkeletonFiles()
    {
        return $this->skeletonFiles;
    }

    /**
     * @param SkeletonFile[]|array $skeletonFiles
     *
     * @return $this
     */
    public function setSkeletonFiles($skeletonFiles)
    {
        $this->skeletonFiles = $skeletonFiles;

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
}
