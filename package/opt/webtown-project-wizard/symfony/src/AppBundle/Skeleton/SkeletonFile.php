<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 21:56
 */

namespace AppBundle\Skeleton;

use Symfony\Component\Finder\SplFileInfo;

class SkeletonFile
{
    /**
     * @var SplFileInfo
     */
    protected $fileInfo;

    /**
     * @var string $contents
     */
    protected $contents;

    /**
     * SkeletonFile constructor.
     * @param SplFileInfo $fileInfo
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }

    /**
     * @return SplFileInfo
     */
    public function getFileInfo()
    {
        return $this->fileInfo;
    }

    /**
     * @param SplFileInfo $fileInfo
     *
     * @return $this
     */
    public function setFileInfo($fileInfo)
    {
        $this->fileInfo = $fileInfo;

        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents === null ? $this->fileInfo->getContents() : $this->contents ;
    }

    /**
     * @param string $contents
     *
     * @return $this
     */
    public function setContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }
}
