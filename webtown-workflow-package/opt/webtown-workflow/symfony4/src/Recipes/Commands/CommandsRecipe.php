<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 16:55
 */

namespace App\Recipes\Commands;

use App\Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use App\Webtown\WorkflowBundle\Event\ConfigurationEvents;
use App\Webtown\WorkflowBundle\Event\RegisterEventListenersInterface;
use App\Webtown\WorkflowBundle\Recipes\HiddenRecipe;
use App\Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\SkeletonHelper;
use App\Webtown\WorkflowBundle\Skeleton\SkeletonTwigFileInfo;
use App\Webtown\WorkflowBundle\Skeleton\TemplateTwigFileInfo;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;

class CommandsRecipe extends HiddenRecipe implements RegisterEventListenersInterface
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
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return SkeletonFile[]|array
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
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return ExecutableSkeletonFile
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
     * @param string $tempFile the template filename
     *
     * @throws \ReflectionException
     *
     * @return SplFileInfo
     */
    protected function getTempSkeletonFileInfo($tempFile)
    {
        $refClass = new \ReflectionClass($this);
        $skeletonsPath = \dirname($refClass->getFileName()) . DIRECTORY_SEPARATOR . SkeletonHelper::TEMPLATES_DIR;
        $tmpFileInfo = new TemplateTwigFileInfo(
            $skeletonsPath . DIRECTORY_SEPARATOR . $tempFile,
            '',
            $tempFile,
            SkeletonHelper::generateTwigNamespace(new \ReflectionClass($this))
        );

        return $tmpFileInfo;
    }
}
