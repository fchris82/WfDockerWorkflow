<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 11:31
 */

namespace App\Webtown\WfBaseSystemRecipesBundle\SystemRecipes\MakefileExtension;

use App\Webtown\WorkflowBundle\Configuration\Builder;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Exception\SkipRecipeException;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use App\Webtown\WorkflowBundle\Recipes\SystemRecipe;
use App\Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Recipe
 *
 * You can insert Docker Compose configuration in the project config file:
 * <code>
 *  makefile:
 *      - ~/dev.mk
 * <code>
 */
class MakefileExtensionRecipe extends SystemRecipe
{
    const NAME = 'makefile';

    public function getName()
    {
        return static::NAME;
    }

    public function getConfig()
    {
        $rootNode = BaseRecipe::getConfig();

        $rootNode
            ->info('<comment>You can add extra <info>makefile files</info>. You have to set absolute path, and you can use the <info>%wf.project_path%</info> placeholder or <info>~</info> (your home directory). You can use only these two path!</comment>')
            ->example('~/dev.mk')
            ->scalarPrototype()->end()
        ;

        return $rootNode;
    }

    /**
     * Register extra makefiles contents
     *
     * @param PostBuildSkeletonFilesEvent $event
     */
    protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event)
    {
        $buildConfig = $event->getBuildConfig();

        foreach ($buildConfig[static::NAME] as $n => $makefile) {
            $filename = sprintf('%d.makefile', $n);
            $fileInfo = new SplFileInfo($filename, '', $filename);

            $skeletonFile = new MakefileSkeletonFile($fileInfo);
            $skeletonFile->setContents(file_get_contents($makefile));
            $event->addSkeletonFile($skeletonFile);
        }
    }
}