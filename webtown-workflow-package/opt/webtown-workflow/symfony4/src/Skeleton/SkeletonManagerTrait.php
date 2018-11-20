<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 13:15
 */

namespace App\Skeleton;

use App\Exception\CircularReferenceException;
use App\Exception\SkipSkeletonFileException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait SkeletonManagerTrait
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $twigSkeletonNamespace;

    /**
     * @param $templateVars
     * @param array $buildConfig
     *
     * @return array|SkeletonFile[]
     *
     * @throws \Exception
     */
    protected function buildSkeletonFiles($templateVars, $buildConfig = [])
    {
        $skeletonFiles = [];
        /** @var SplFileInfo $skeletonFileInfo */
        foreach ($this->getSkeletonFinder() as $skeletonFileInfo) {
            try {
                $skeletonFile = $this->buildSkeletonFile($skeletonFileInfo, $buildConfig);
                $skeletonFile->setContents($this->parseTemplateFile(
                    $skeletonFileInfo,
                    $templateVars
                ));
                $skeletonFiles[] = $skeletonFile;
            } catch (SkipSkeletonFileException $exception) {
            }
        }

        return $skeletonFiles;
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param array $buildConfig
     *
     * @return SkeletonFile
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, $buildConfig = [])
    {
        return new SkeletonFile($fileInfo);
    }

    protected function parseTemplateFile(SplFileInfo $templateFile, $templateVariables)
    {
        foreach ($this->twig->getLoader()->getPaths($this->twigSkeletonNamespace) as $path) {
            if (strpos($templateFile->getPathname(), realpath($path)) === 0) {
                $twigPath = str_replace(
                    realpath($path),
                    '',
                    $templateFile->getPathname()
                );
                $file = sprintf('@%s/%s', $this->twigSkeletonNamespace, $twigPath);

                return $this->twig->render($file, $templateVariables);
            }
        }

        throw new \Exception('Twig path not found');
    }

    protected function getSkeletonFinder()
    {
        $skeletonFinder = Finder::create()
            ->files()
            ->in(static::getSkeletonPaths())
            ->ignoreDotFiles(false);

        return $skeletonFinder;
    }

    /**
     * @return Finder
     *
     * @throws \ReflectionException
     * @throws CircularReferenceException
     */
    public static function getSkeletonPaths()
    {
        $skeletonPaths = [];
        foreach (static::getSkeletonParents() as $class) {
            $skeletonPaths = array_merge($skeletonPaths, $class::getSkeletonPaths());
        }
        $uniquePaths = array_unique($skeletonPaths);
        if ($uniquePaths != $skeletonPaths) {
            throw new CircularReferenceException('There are circular references in skeleton path.');
        }

        $refClass = new \ReflectionClass(static::class);
        $skeletonPath = dirname($refClass->getFileName()) . '/skeletons';
        if (is_dir($skeletonPath)) {
            $skeletonPaths[] = $skeletonPath;
        }

        return $skeletonPaths;
    }

    /**
     * @return array|string[]
     */
    public static function getSkeletonParents()
    {
        return [];
    }
}
