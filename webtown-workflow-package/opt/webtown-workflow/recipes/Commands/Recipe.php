<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 16:55
 */

namespace Recipes\Commands;

use Recipes\HiddenRecipe;
use App\Skeleton\ExecutableSkeletonFile;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Finder\SplFileInfo;

class Recipe extends HiddenRecipe
{
    const NAME = 'commands';

    public function getName()
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function build($projectPath, $recipeConfig, $globalConfig)
    {
        // Start creating .sh files
        $tmpSkeletonFileInfo = $this->getTempSkeletonFileInfo('bin.sh');

        // Collect the skeleton files
        $skeletonFiles = [];
        // Collect the targets for makefile
        $makefileTargets = [];
        foreach ($globalConfig['commands'] as $commandName => $commands) {
            $templateVars = $this->getTemplateVars($projectPath, $recipeConfig, $globalConfig);
            $templateVars['commands'] = $commands;
            $skeletonFile = $this->createSkeletonFile($tmpSkeletonFileInfo, $commandName, $templateVars);
            $skeletonFiles[] = $skeletonFile;
            $makefileTargets[$commandName] = $skeletonFile->getFileInfo()->getRelativePathname();
        }

        // Create makefile
        $recipeConfig['makefileTargets'] = $makefileTargets;
        $skeletonFiles = array_merge($skeletonFiles, parent::build($projectPath, $recipeConfig, $globalConfig));

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
