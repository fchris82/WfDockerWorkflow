<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 21:56
 */

namespace App\Skeleton;

use Symfony\Component\Finder\SplFileInfo;

class SkeletonFile
{
    /**
     * @var SplFileInfo
     */
    protected $baseFileInfo;

    /**
     * @var string|null
     */
    protected $relativePath;

    /**
     * @var string|null
     */
    protected $fileName;

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
        $this->baseFileInfo = $fileInfo;
    }

    /**
     * @return SplFileInfo
     */
    public function getBaseFileInfo()
    {
        return $this->baseFileInfo;
    }

    /**
     * @param SplFileInfo $baseFileInfo
     *
     * @return $this
     */
    public function setBaseFileInfo($baseFileInfo)
    {
        $this->baseFileInfo = $baseFileInfo;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelativePath()
    {
        return $this->relativePath ?: $this->getBaseFileInfo()->getRelativePath();
    }

    /**
     * @param string|null $relativePath
     *
     * @return $this
     */
    public function setRelativePath($relativePath)
    {
        $this->relativePath = $relativePath;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFileName()
    {
        return $this->fileName ?: $this->getBaseFileInfo()->getFilename();
    }

    /**
     * @param string|null $fileName
     *
     * @return $this
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelativePathname()
    {
        return $this->relativePath
            ? $this->getRelativePath() . DIRECTORY_SEPARATOR . $this->getFileName()
            : $this->getBaseFileInfo()->getRelativePathname();
    }

    /**
     * @param string|null $relativePathname
     *
     * @return $this
     */
    public function setRelativePathname($relativePathname)
    {
        $this->relativePathname = $relativePathname;

        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents === null ? $this->baseFileInfo->getContents() : $this->contents ;
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
