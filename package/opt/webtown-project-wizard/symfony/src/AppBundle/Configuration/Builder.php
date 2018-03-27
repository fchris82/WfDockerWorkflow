<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 17:20
 */

namespace AppBundle\Configuration;


use AppBundle\Exception\SkipRecipeException;
use AppBundle\Skeleton\DockerComposeSkeletonFile;
use AppBundle\Skeleton\ExecutableSkeletonFile;
use AppBundle\Skeleton\MakefileSkeletonFile;
use AppBundle\Skeleton\SkeletonFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class Builder
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var RecipeManager
     */
    protected $recipeManager;

    /**
     * @var string
     */
    protected $targetDirectory;

    protected $makefiles = [];

    protected $dockerComposeFiles = [];

    /**
     * Builder constructor.
     * @param Filesystem $fileSystem
     * @param RecipeManager $recipeManager
     */
    public function __construct(Filesystem $fileSystem, RecipeManager $recipeManager)
    {
        $this->fileSystem = $fileSystem;
        $this->recipeManager = $recipeManager;
    }

    /**
     * @return mixed
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @param string $targetDirectory
     *
     * @return $this
     */
    public function setTargetDirectory($targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;

        return $this;
    }

    /**
     * @param array $config
     * @param string $projectPath
     * @param string $configHash
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function build($config, $projectPath, $configHash)
    {
        if (!$this->targetDirectory) {
            throw new \InvalidArgumentException('You have to call first the `setCachePath` function!');
        }
        // If the filename or directoryname starts with dot, we keep it. Eg: .data directory
        $this->fileSystem->remove(Finder::create()->in($projectPath . '/' . $this->targetDirectory)->depth(0));

        // BASE
        $this->buildRecipe($projectPath, 'base', $config, $config);
        // PUBLIC RECIPES
        foreach ($config['recipes'] as $recipe => $recipeConfig) {
            $this->buildRecipe($projectPath, $recipe, $recipeConfig, $config);
        }

        // COMMANDS
        $this->buildRecipe($projectPath, 'bin', [], $config);
        // INCLUDED FILES
        $this->includeExtraFiles($config);
        // DOCKER COMPOSE EXTENSION
        $this->buildRecipe($projectPath, 'docker_compose_extension', [], $config);
        // POST BASE
        $this->buildRecipe($projectPath, 'post_base', [
            'services' => $this->parseAllDockerServices($projectPath, $this->dockerComposeFiles),
        ], $config);

        $this->buildProjectMakefile($projectPath, $configHash);
    }

    /**
     * @param string $projectPath
     * @param string $recipeName
     * @param array $recipeConfig
     * @param array $globalConfig
     *
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \ReflectionException
     * @throws \Exception
     */
    protected function buildRecipe($projectPath, $recipeName, $recipeConfig, $globalConfig = [])
    {
        try {
            /** @var BaseRecipe $recipe */
            $recipe = $this->recipeManager->getRecipe($recipeName);

            /** @var SkeletonFile[] $skeletonFiles */
            $skeletonFiles = $recipe->build($projectPath, $recipeConfig, $globalConfig);

            foreach ($skeletonFiles as $skeletonFile) {
                $fileTarget = $this->getRelativeTargetFilePath($recipeName, $skeletonFile->getFileInfo());
                $fileFullTarget = $projectPath . '/' . $fileTarget;
                $this->fileSystem->dumpFile($fileFullTarget, $skeletonFile->getContents());

                switch (true) {
                    case $skeletonFile instanceof MakefileSkeletonFile:
                        $this->makefiles[] = $fileTarget;
                        break;
                    case $skeletonFile instanceof DockerComposeSkeletonFile:
                        $this->dockerComposeFiles[] = $fileTarget;
                        break;
                    case $skeletonFile instanceof ExecutableSkeletonFile:
                        $this->fileSystem->chmod($fileFullTarget, $skeletonFile->getPermission());
                        break;
                }
            }
        } catch (SkipRecipeException $e) {
            // do nothing
        }
    }

    /**
     * Handle the extra configuration files. Like additional `makefile` or `docker-compose.yml` file.
     *
     * @param $config
     */
    protected function includeExtraFiles($config)
    {
        $this->dockerComposeFiles = array_merge(
            $this->dockerComposeFiles,
            $config['docker_compose']['include']
        );
        $this->makefiles = array_merge($this->makefiles, $config['makefile']);
    }

    /**
     * Build the relative target path, like: `.wf/mysql/docker-compose.yml`
     *
     * @param string      $recipeName
     * @param SplFileInfo $fileInfo
     *
     * @return string
     */
    protected function getRelativeTargetFilePath($recipeName, SplFileInfo $fileInfo)
    {
        return sprintf('%s/%s/%s', $this->targetDirectory, $recipeName, $fileInfo->getRelativePathname());
    }

    /**
     * Find all docker service name through parsing the all included docker-compose.yml file.
     *
     * @param array $dockerComposeFiles
     *
     * @return array
     */
    protected function parseAllDockerServices($projectPath, $dockerComposeFiles)
    {
        $services = [];
        foreach ($dockerComposeFiles as $dockerComposeFile) {
            $config = Yaml::parse(file_get_contents(
                $projectPath . '/' . $dockerComposeFile
            ));
            if (isset($config['services'])) {
                $services = array_unique(array_merge($services, array_keys($config['services'])));
            }
        }

        return $services;
    }

    /**
     * @param string $projectPath
     * @param string $versionHash The CRC32 hash of config yml file
     */
    protected function buildProjectMakefile($projectPath, $versionHash)
    {
        $path = sprintf('%s/%s/%s.mk', $projectPath, $this->targetDirectory, $versionHash);
        $includeMakefiles = $this->makefileMultilineFormatter('include %s', $this->makefiles);
        $dockerComposeFiles = array_map(function($v) {
            // If the path start with `/` or `~` we won't change, else we put the project path before it
            return in_array($v[0], ['/', '~']) ? $v : '$(PROJECT_WORKING_DIRECTORY)/' . $v;
        }, $this->dockerComposeFiles);
        $dockerComposeFiles = $this->makefileMultilineFormatter('DOCKER_CONFIG_FILES := %s', $dockerComposeFiles);
        $contents = <<<EOS
PROJECT_WORKING_DIRECTORY := \$\${PWD}
WF_TARGET_DIRECTORY := $this->targetDirectory

# Makefiles
$includeMakefiles

# Docker files
$dockerComposeFiles

ORIGINAL_CMD_DOCKER_ENV := $(CMD_DOCKER_ENV)
define CMD_DOCKER_ENV
    $(ORIGINAL_CMD_DOCKER_ENV) \
    WF_TARGET_DIRECTORY=$(WF_TARGET_DIRECTORY)
endef

define CMD_DOCKER_BASE
    $(CMD_DOCKER_ENV) docker-compose \
        -p $(DOCKER_BASENAME) \
        $(foreach file,$(DOCKER_CONFIG_FILES),-f $(file)) \
        --project-directory $(CURDIR)
endef
define CMD_DOCKER_RUN
    $(CMD_DOCKER_BASE) run --rm
endef
# If you want to run without user (as root), use the: `$(CMD_DOCKER_RUN) $(DOCKER_CLI_NAME) <cmd>` instead of `$(CMD_DOCKER_RUN_CLI) <cmd>`
define CMD_DOCKER_RUN_CLI
    $(CMD_DOCKER_RUN) --user $(DOCKER_USER) $(DOCKER_CLI_NAME)
endef
define CMD_DOCKER_EXEC
    $(CMD_DOCKER_BASE) exec
endef
# If you want to run without user (as root), use the: `$(CMD_DOCKER_EXEC) $(DOCKER_CLI_NAME) <cmd>` instead of `$(CMD_DOCKER_EXEC_CLI) <cmd>`
define CMD_DOCKER_EXEC_CLI
    $(CMD_DOCKER_EXEC) --user $(DOCKER_USER) $(DOCKER_PSEUDO_TTY) $(DOCKER_CLI_NAME)
endef
EOS;

        $this->fileSystem->dumpFile($path, $contents);
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
        $glue = sprintf(" \\\n%s", str_repeat(' ', strlen($emptyPattern)));

        return sprintf($pattern, implode($glue, $array));
    }
}
