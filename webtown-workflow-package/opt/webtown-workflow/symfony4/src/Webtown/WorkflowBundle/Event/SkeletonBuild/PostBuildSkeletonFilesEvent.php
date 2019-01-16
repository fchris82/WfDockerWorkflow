<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 13:10
 */

namespace App\Webtown\WorkflowBundle\Event\SkeletonBuild;

use App\Webtown\WorkflowBundle\Event\NamespacedEvent;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;

class PostBuildSkeletonFilesEvent extends NamespacedEvent
{
    /**
     * @var array|SkeletonFile[]
     */
    protected $skeletonFiles;

    /**
     * @var array
     */
    protected $skeletonVars;

    /**
     * @var array
     */
    protected $buildConfig;

    /**
     * PostBuildSkeletonFilesEvent constructor.
     *
     * @param $namespace
     * @param array                $skeletonVars
     * @param array                $buildConfig
     * @param SkeletonFile[]|array $skeletonFiles
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($namespace, $skeletonFiles, array $skeletonVars, array $buildConfig)
    {
        parent::__construct($namespace);
        $this->skeletonFiles = $skeletonFiles;
        $this->skeletonVars = $skeletonVars;
        $this->buildConfig = $buildConfig;
    }

    /**
     * @return SkeletonFile[]|array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getSkeletonFiles()
    {
        return $this->skeletonFiles;
    }

    /**
     * @param SkeletonFile[]|array $skeletonFiles
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonFiles($skeletonFiles)
    {
        $this->skeletonFiles = $skeletonFiles;

        return $this;
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
}
