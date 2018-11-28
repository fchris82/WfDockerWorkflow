<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.24.
 * Time: 21:56
 */

namespace App\Skeleton\FileType;

use Symfony\Component\Finder\SplFileInfo;

class SkeletonFile
{
    const HANDLE_EXISTING_FULL_OVERWRITE = 0;
    const HANDLE_EXISTING_APPEND = 1;

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
     * @var string|null
     */
    protected $fullTargetPathname;

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var int
     */
    protected $handleExisting;

    /**
     * SkeletonFile constructor.
     *
     * @param SplFileInfo $fileInfo
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->baseFileInfo = $fileInfo;
        $this->handleExisting = static::HANDLE_EXISTING_FULL_OVERWRITE;
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
        $this->relativePath = rtrim($relativePath, \DIRECTORY_SEPARATOR);

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
     * @param string|null $newFileName
     *
     * @return $this
     */
    public function setFileName($newFileName)
    {
        $relativeDirectory = rtrim($this->getRelativePath(), \DIRECTORY_SEPARATOR);
        $this->setRelativePathname($relativeDirectory . \DIRECTORY_SEPARATOR . $newFileName);

        $this->fileName = $newFileName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRelativePathname()
    {
        return $this->relativePath
            ? $this->getRelativePath() . \DIRECTORY_SEPARATOR . $this->getFileName()
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
     * @return null|string
     */
    public function getFullTargetPathname(): ?string
    {
        return $this->fullTargetPathname ?: $this->getRelativePathname();
    }

    /**
     * @param null|string $fullTargetPathname
     *
     * @return $this
     */
    public function setFullTargetPathname(?string $fullTargetPathname)
    {
        $this->fullTargetPathname = $fullTargetPathname;

        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return null === $this->contents ? $this->baseFileInfo->getContents() : $this->contents;
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

    /**
     * @return int
     */
    public function getHandleExisting(): int
    {
        return $this->handleExisting;
    }

    /**
     * @param int $handleExisting
     *
     * @return $this
     */
    public function setHandleExisting(int $handleExisting)
    {
        $this->handleExisting = $handleExisting;

        return $this;
    }

    public function move($directory)
    {
        $directory = rtrim($directory, \DIRECTORY_SEPARATOR);
        $this->setFullTargetPathname($directory . \DIRECTORY_SEPARATOR . $this->getRelativePathname());
    }
}
