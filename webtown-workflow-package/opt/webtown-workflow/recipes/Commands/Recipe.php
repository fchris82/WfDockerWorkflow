<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 16:55
 */

namespace Recipes\Commands;

use App\Event\Configuration\BuildInitEvent;
use App\Event\ConfigurationEvents;
use App\Event\RegisterEventListenersInterface;
use Recipes\HiddenRecipe;
use App\Skeleton\FileType\ExecutableSkeletonFile;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;

class Recipe extends HiddenRecipe implements RegisterEventListenersInterface
{
    const NAME = 'commands';

    /**
     * @var array
     */
    protected $globalConfig;

    public function getName()
    {
        return static::NAME;
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(ConfigurationEvents::BUILD_INIT, [$this, 'init']);
    }

    public function init(BuildInitEvent $event)
    {
        $this->globalConfig = $event->getConfig();
    }

    /**
     * @param $templateVars
     * @param array $buildConfig
     *
     * @return \App\Skeleton\FileType\SkeletonFile[]|array
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function buildSkeletonFiles($templateVars, $buildConfig = [])
    {
        // Start creating .sh files
        $tmpSkeletonFileInfo = $this->getTempSkeletonFileInfo('bin.sh');

        // Collect the skeleton files
        $skeletonFiles = [];
        // Collect the targets for makefile
        $makefileTargets = [];
        foreach ($this->globalConfig[static::NAME] as $commandName => $commands) {
            $commandTemplateVars = $templateVars;
            $commandTemplateVars['commands'] = $commands;
            $skeletonFile = $this->createSkeletonFile($tmpSkeletonFileInfo, $commandName, $commandTemplateVars);
            $skeletonFiles[] = $skeletonFile;
            $makefileTargets[$commandName] = $skeletonFile->getRelativePathname();
        }

        // Create makefile
        $templateVars['makefileTargets'] = $makefileTargets;
        $skeletonFiles = array_merge($skeletonFiles, parent::buildSkeletonFiles($templateVars, $buildConfig));

        return $skeletonFiles;
    }

    /**
     * Create an ExecutableSkeletonFile from a template FileInfo and other parameters.
     *
     * @param SplFileInfo $tmpFileInfo
     * @param string      $commandName
     * @param array       $templateVars
     *
     * @return ExecutableSkeletonFile
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function createSkeletonFile(SplFileInfo $tmpFileInfo, $commandName, $templateVars)
    {
        $fileName = $commandName . '.sh';
        $newSplFileInfo = new SplFileInfo($fileName, '', $fileName);
        $templateContent = $this->parseTemplateFile(
            $tmpFileInfo,
            $templateVars
        );
        $outputFormatter = new OutputFormatter(true);
        $skeletonFile = new ExecutableSkeletonFile($newSplFileInfo);
        $skeletonFile->setContents($outputFormatter->format($templateContent));

        return $skeletonFile;
    }

    /**
     * @param string $tempFile The template filename.
     *
     * @return SplFileInfo
     *
     * @throws \ReflectionException
     */
    protected function getTempSkeletonFileInfo($tempFile)
    {
        $refClass = new \ReflectionClass($this);
        $skeletonsPath = dirname($refClass->getFileName()) . '/template';
        $tmpFileInfo = new SplFileInfo($skeletonsPath . '/' . $tempFile, '', $tempFile);

        return $tmpFileInfo;
    }
}
