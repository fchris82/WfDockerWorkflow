<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 13:15
 */

namespace App\Skeleton;

use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use App\Event\SkeletonBuildBaseEvents;
use App\Exception\CircularReferenceException;
use App\Exception\SkipSkeletonFileException;
use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

trait SkeletonManagerTrait
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var string
     */
    protected $twigSkeletonNamespace;

    abstract protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event);
    abstract protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $event);
    abstract protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event);
    abstract protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event);

    /**
     * @param $templateVars
     * @param array $buildConfig
     *
     * @return array|SkeletonFile[]
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function buildSkeletonFiles($templateVars, $buildConfig = [])
    {
        $preBuildEvent = new PreBuildSkeletonFilesEvent($this, $templateVars, $buildConfig);
        $this->eventBeforeBuildFiles($preBuildEvent);
        $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::BEFORE_BUILD_FILES, $preBuildEvent);

        $skeletonFiles = [];
        $baseSkeletonFileInfos = $preBuildEvent->getSkeletonFileInfos() ?: $this->getSkeletonFinder($buildConfig);
        $templateVars = $preBuildEvent->getTemplateVars();
        $buildConfig = $preBuildEvent->getBuildConfig();

        /** @var SplFileInfo $skeletonFileInfo */
        foreach ($baseSkeletonFileInfos as $skeletonFileInfo) {
            try {
                $preEvent = new PreBuildSkeletonFileEvent($this, $skeletonFileInfo, $templateVars, $buildConfig);
                $this->eventBeforeBuildFile($preEvent);
                $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::BEFORE_BUILD_FILE, $preEvent);
                $skeletonFile = $preEvent->getSkeletonFile()
                    ?: $this->buildSkeletonFile($preEvent->getSourceFileInfo(), $preEvent->getBuildConfig());
                $skeletonFile->setContents($this->parseTemplateFile(
                    $skeletonFileInfo,
                    $preEvent->getTemplateVars()
                ));
                $postEvent = new PostBuildSkeletonFileEvent($this, $skeletonFile, $skeletonFileInfo, $preEvent->getTemplateVars(), $preEvent->getBuildConfig());
                $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::AFTER_BUILD_FILE, $postEvent);
                $this->eventAfterBuildFile($postEvent);
                $skeletonFiles[] = $postEvent->getSkeletonFile();
            } catch (SkipSkeletonFileException $exception) {
            }
        }

        $postBuildEvent = new PostBuildSkeletonFilesEvent($this, $skeletonFiles, $templateVars, $buildConfig);
        $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::AFTER_BUILD_FILES, $postBuildEvent);
        $this->eventAfterBuildFiles($postBuildEvent);

        return $postBuildEvent->getSkeletonFiles();
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param array $buildConfig
     *
     * @return SkeletonFile
     * 
     * @throws SkipSkeletonFileException
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, $buildConfig = [])
    {
        return new SkeletonFile($fileInfo);
    }

    /**
     * @param SplFileInfo $templateFile
     * @param array $templateVariables
     *
     * @return string
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
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

    protected function getSkeletonFinder($buildConfig)
    {
        $paths = static::getSkeletonPaths($buildConfig);
        if (count($paths) == 0) {
            return [];
        }

        $skeletonFinder = Finder::create()
            ->files()
            ->in($paths)
            ->ignoreDotFiles(false);

        return $skeletonFinder;
    }

    /**
     * @param array $buildConfig
     *
     * @return Finder
     *
     * @throws CircularReferenceException
     * @throws \ReflectionException
     */
    public static function getSkeletonPaths($buildConfig = [])
    {
        $skeletonPaths = [];
        foreach (static::getSkeletonParents() as $class) {
            $skeletonPaths = array_merge($skeletonPaths, $class::getSkeletonPaths($buildConfig));
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
