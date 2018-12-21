<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 17:39
 */

namespace App\Webtown\WorkflowBundle\Skeleton;

use Symfony\Component\Finder\SplFileInfo;

class SkeletonTwigFileInfo extends SplFileInfo
{
    protected $twigNamespace;

    public function __construct(string $file, string $relativePath, string $relativePathname, string $twigNamespace)
    {
        $this->twigNamespace = $twigNamespace;
        parent::__construct($file, $relativePath, $relativePathname);
    }

    public static function create(SplFileInfo $fileInfo, string $twigNamespace): self
    {
        return new static($fileInfo->getPathname(), $fileInfo->getRelativePath(), $fileInfo->getRelativePathname(), $twigNamespace);
    }

    /**
     * @return string
     */
    public function getTwigNamespace(): string
    {
        return $this->twigNamespace;
    }

    public function getTwigPath(): string
    {
        return sprintf('@%s/%s', $this->twigNamespace, $this->getRelativePathname());
    }
}
