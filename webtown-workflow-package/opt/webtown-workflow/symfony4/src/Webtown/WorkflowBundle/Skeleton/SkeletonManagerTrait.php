<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 13:15
 */

namespace App\Webtown\WorkflowBundle\Skeleton;

use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use App\Webtown\WorkflowBundle\Exception\CircularReferenceException;
use App\Webtown\WorkflowBundle\Exception\SkipSkeletonFileException;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
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

    abstract protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event);

    abstract protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $event);

    abstract protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event);

    abstract protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event);

    /**
     * @param $templateVars
     * @param array $buildConfig
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return array|SkeletonFile[]
     */
    protected function buildSkeletonFiles($templateVars, $buildConfig = [])
    {
        $preBuildEvent = new PreBuildSkeletonFilesEvent($this, $templateVars, $buildConfig);
        $this->eventBeforeBuildFiles($preBuildEvent);
        $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::BEFORE_BUILD_FILES, $preBuildEvent);

        $skeletonFiles = [];
        $baseSkeletonFileInfos = $preBuildEvent->getSkeletonFileInfos() ?: $this->getSkeletonFiles($buildConfig);
        $templateVars = $preBuildEvent->getSkeletonVars();
        $buildConfig = $preBuildEvent->getBuildConfig();

        /** @var SkeletonTwigFileInfo $skeletonFileInfo */
        foreach ($baseSkeletonFileInfos as $skeletonFileInfo) {
            try {
                $preEvent = new PreBuildSkeletonFileEvent($this, $skeletonFileInfo, $templateVars, $buildConfig);
                $this->eventBeforeBuildFile($preEvent);
                $this->eventDispatcher->dispatch(SkeletonBuildBaseEvents::BEFORE_BUILD_FILE, $preEvent);
                $skeletonFile = $preEvent->getSkeletonFile()
                    ?: $this->buildSkeletonFile($preEvent->getSourceFileInfo(), $preEvent->getBuildConfig());
                $skeletonFile->setContents($this->parseTemplateFile(
                    $skeletonFileInfo,
                    $preEvent->getSkeletonVars()
                ));
                $postEvent = new PostBuildSkeletonFileEvent($this, $skeletonFile, $skeletonFileInfo, $preEvent->getSkeletonVars(), $preEvent->getBuildConfig());
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
     * @param array       $buildConfig
     *
     * @throws SkipSkeletonFileException
     *
     * @return SkeletonFile
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, $buildConfig = [])
    {
        return new SkeletonFile($fileInfo);
    }

    /**
     * @param SkeletonTwigFileInfo $templateFile
     * @param array                $templateVariables
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return string
     */
    protected function parseTemplateFile(SkeletonTwigFileInfo $templateFile, $templateVariables)
    {
        return $this->twig->render($templateFile->getTwigPath(), $templateVariables);
    }

    protected function getSkeletonFiles($buildConfig)
    {
        $pathsWithTwigNamespace = static::getSkeletonPaths($buildConfig);
        if (0 == \count($pathsWithTwigNamespace)) {
            return [];
        }

        $skeletonFiles = [];
        // We don't handle the overridden files here, just later. You can use the original or the new file in an event handler.
        foreach ($pathsWithTwigNamespace as $twigNamespace => $path) {
            $skeletonFinder = Finder::create()
                ->files()
                ->in($path)
                ->ignoreDotFiles(false);

            foreach ($skeletonFinder as $fileInfo) {
                $skeletonFiles[] = SkeletonTwigFileInfo::create($fileInfo, $twigNamespace);
            }
        }

        return $skeletonFiles;
    }

    /**
     * @param array $buildConfig
     *
     * @throws CircularReferenceException
     * @throws \ReflectionException
     *
     * @return array|string[]
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
        $skeletonPath = \dirname($refClass->getFileName()) . \DIRECTORY_SEPARATOR . SkeletonHelper::SKELETONS_DIR;
        if (is_dir($skeletonPath)) {
            $skeletonPaths[SkeletonHelper::generateTwigNamespace($refClass)] = $skeletonPath;
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
