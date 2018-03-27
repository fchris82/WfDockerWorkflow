<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:00
 */

namespace AppBundle\Configuration;

use AppBundle\Skeleton\DockerComposeSkeletonFile;
use AppBundle\Skeleton\MakefileSkeletonFile;
use AppBundle\Skeleton\SkeletonFile;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

abstract class BaseRecipe
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * BaseRecipe constructor.
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
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
     * @return array|SkeletonFile[]
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function build($projectPath, $recipeConfig, $globalConfig)
    {
        $skeletonFiles = [];
        $templateVars = $this->getTemplateVars($projectPath, $recipeConfig, $globalConfig);

        /** @var SplFileInfo $skeletonFileInfo */
        foreach ($this->getSkeletons() as $skeletonFileInfo) {
            $skeletonFile = $this->buildSkeletonFile($skeletonFileInfo, $recipeConfig);
            $skeletonFile->setContents($this->parseTemplateFile(
                $skeletonFileInfo,
                $templateVars
            ));
            $skeletonFiles[] = $skeletonFile;
        }

        return $skeletonFiles;
    }

    /**
     * @param SplFileInfo $templateFile
     * @param array $templateVariables
     *
     * @return string
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Exception
     */
    protected function parseTemplateFile(SplFileInfo $templateFile, array $templateVariables)
    {
        foreach ($this->twig->getLoader()->getPaths('recipe') as $path) {
            if (strpos($templateFile->getPathname(), realpath($path)) === 0) {
                $twigPath = str_replace(
                    realpath($path),
                    '',
                    $templateFile->getPathname()
                );
                $file = sprintf('@recipe/%s', $twigPath);

                return $this->twig->render($file, $templateVariables);
            }
        }

        throw new \Exception('Twig path not found');
    }

    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        if (is_string($recipeConfig)) {
            $recipeConfig = ['value' => $recipeConfig];
        }

        return array_merge([
            'config' => $globalConfig,
            'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/' . $this->getName(),
        ], $recipeConfig);
    }

    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        switch ($fileInfo->getFilename()) {
            case 'makefile':
                return new MakefileSkeletonFile($fileInfo);
            case 'docker-compose.yml':
                return new DockerComposeSkeletonFile($fileInfo);
        }

        return new SkeletonFile($fileInfo);
    }

    protected function isMakefile(SplFileInfo $fileInfo)
    {
        return $fileInfo->getFilename() == 'makefile';
    }

    protected function isDockerComposeFile(SplFileInfo $fileInfo)
    {
        return $fileInfo->getFilename() == 'docker-compose.yml';
    }

    /**
     * @return Finder
     *
     * @throws \ReflectionException
     */
    protected function getSkeletons()
    {
        $refClass = new \ReflectionClass($this);
        return Finder::create()
            ->files()
            ->in(dirname($refClass->getFileName()) . '/skeletons')
            ->ignoreDotFiles(false);
    }
}
