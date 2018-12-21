<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:00
 */

namespace App\Webtown\WorkflowBundle\Recipes;

use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\SkeletonManagerTrait;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;

abstract class BaseRecipe
{
    use SkeletonManagerTrait;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * BaseRecipe constructor.
     *
     * @param \Twig_Environment        $twig
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(\Twig_Environment $twig, EventDispatcherInterface $eventDispatcher)
    {
        $this->twig = $twig;
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract public function getName();

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    public function getConfig()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->getName());

        return $rootNode;
    }

    /**
     * @param $projectPath
     * @param $recipeConfig
     * @param $globalConfig
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return array|SkeletonFile[]
     */
    public function build($projectPath, $recipeConfig, $globalConfig)
    {
        $templateVars = $this->getSkeletonVars($projectPath, $recipeConfig, $globalConfig);

        return $this->buildSkeletonFiles($templateVars, $recipeConfig);
    }

    public function getSkeletonVars($projectPath, $recipeConfig, $globalConfig)
    {
        if (\is_string($recipeConfig)) {
            $recipeConfig = ['value' => $recipeConfig];
        }

        return array_merge([
            'config' => $globalConfig,
            'project_path' => $projectPath,
            'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/' . $this->getName(),
            'env' => $_ENV,
        ], $recipeConfig);
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param $config
     *
     * @return DockerComposeSkeletonFile|ExecutableSkeletonFile|MakefileSkeletonFile|SkeletonFile
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        if ($this->isDockerComposeFile($fileInfo)) {
            return new DockerComposeSkeletonFile($fileInfo);
        }
        if ($this->isMakefile($fileInfo)) {
            return new MakefileSkeletonFile($fileInfo);
        }
        if ($this->isExecutableFile($fileInfo)) {
            return new ExecutableSkeletonFile($fileInfo);
        }

        return new SkeletonFile($fileInfo);
    }

    protected function isMakefile(SplFileInfo $fileInfo)
    {
        return 'makefile' == $fileInfo->getFilename();
    }

    protected function isDockerComposeFile(SplFileInfo $fileInfo)
    {
        return 0 === strpos($fileInfo->getFilename(), 'docker-compose')
            && 'yml' == $fileInfo->getExtension();
    }

    protected function isExecutableFile(SplFileInfo $fileInfo)
    {
        return $fileInfo->isExecutable();
    }

    public function getDirectoryName()
    {
        return $this->getName();
    }

    /**
     * Formatting helper for makefiles, eg:
     * <code>
     *  # makefileMultilineFormatter('FOO := %s', ['value1', 'value2', 'value3'])
     *  FOO := value1 \
     *         value2 \
     *         value3
     * </code>
     *
     * @param string $pattern `printf` format pattern
     * @param array  $array
     *
     * @return string
     */
    protected function makefileMultilineFormatter($pattern, $array)
    {
        $emptyPattern = sprintf($pattern, '');
        $glue = sprintf(" \\\n%s", str_repeat(' ', \strlen($emptyPattern)));

        return sprintf($pattern, implode($glue, $array));
    }

    protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event)
    {
    }

    protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $preBuildSkeletonFileEvent)
    {
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent)
    {
    }

    protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event)
    {
    }
}
