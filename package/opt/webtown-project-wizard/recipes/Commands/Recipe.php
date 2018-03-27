<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 16:55
 */

namespace Recipes\Commands;

use AppBundle\Configuration\HiddenRecipe;
use AppBundle\Skeleton\SkeletonFile;
use Symfony\Component\Finder\SplFileInfo;

class Recipe extends HiddenRecipe
{
    const NAME = 'bin';

    public function getName()
    {
        return self::NAME;
    }

    public function build($projectPath, $recipeConfig, $globalConfig)
    {
        $tmpSkeletonFileInfo = $this->getTmpSkeletonFileInfo('bin.sh');

        $skeletonFiles = [];
        foreach ($globalConfig['commands'] as $commandName => $commands) {
            $templateVars = $this->getTemplateVars($projectPath, $recipeConfig, $globalConfig);
            $templateVars['commands'] = $commands;
            $skeletonFile[] = $this->createSkeletonFile($tmpSkeletonFileInfo, $commandName, $templateVars);
        }

        return $skeletonFiles;
    }

    protected function createSkeletonFile(SplFileInfo $tmpFileInfo, $commandName, $templateVars)
    {
        $newSplFileInfo = new SplFileInfo('', '', $commandName . '.sh');
        $skeletonFile = new SkeletonFile($newSplFileInfo);
        $skeletonFile->setContents($this->parseTemplateFile(
            $tmpFileInfo,
            $templateVars
        ));

        return $skeletonFile;
    }

    protected function getTmpSkeletonFileInfo($tmpFile)
    {
        $refClass = new \ReflectionClass($this);
        $skeletonsPath = dirname($refClass->getFileName()) . '/skeletons';
        $tmpFileInfo = new SplFileInfo($skeletonsPath . '/' . $tmpFile, '', $tmpFile);

        return $tmpFileInfo;
    }
}
