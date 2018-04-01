<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 17:20
 */

namespace App\Configuration;


use App\Event\BuildInitEvent;
use App\Event\ConfigurationEvents;
use App\Event\DumpEvent;
use App\Event\FinishEvent;
use App\Event\VerboseInfoEvent;
use App\Exception\SkipRecipeException;
use App\Skeleton\DockerComposeSkeletonFile;
use App\Skeleton\ExecutableSkeletonFile;
use App\Skeleton\MakefileSkeletonFile;
use App\Skeleton\SkeletonFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
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
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

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
    public function __construct(Filesystem $fileSystem, RecipeManager $recipeManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->fileSystem = $fileSystem;
        $this->recipeManager = $recipeManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return string
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
     * @return string
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

        // INIT
        foreach ($config['recipes'] as $recipe => $recipeConfig) {
            $this->addRecipeEventSubscribers($projectPath, $recipe, $recipeConfig, $config);
        }
        $initEvent = new BuildInitEvent($config, $projectPath, $this->targetDirectory, $configHash);
        $this->eventDispatcher->dispatch(ConfigurationEvents::BUILD_INIT, $initEvent);
        $initEvent->setConfig($this->configReplaceParameters($initEvent->getConfig(), $initEvent->getParameters()));
        // Init the directory structure
        $this->initDirectoryStructure($initEvent);
        $config = $initEvent->getConfig();

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

        $finishEvent = new FinishEvent($this->fileSystem);
        $this->eventDispatcher->dispatch(ConfigurationEvents::FINISH, $finishEvent);

        return $this->buildProjectMakefile($projectPath, $configHash);
    }

    /**
     * Replace parameters in the values of $config.
     *
     * <code>
     *  %wf.target_directory%/README.md --> .wf/README.md
     * </code>
     *
     * @param mixed $config
     * @param array $parameters
     *
     * @return mixed
     */
    protected function configReplaceParameters($config, $parameters)
    {
        if (is_array($config)) {
            foreach ($config as $key => $value) {
                $config[$key] = $this->configReplaceParameters($value, $parameters);
            }
        } elseif (is_string($config)) {
            return strtr($config, $parameters);
        }

        return $config;
    }

    /**
     * Create directories:
     *  - target
     *  - data
     * Or just clean up.
     *
     * @param BuildInitEvent $initEvent
     */
    protected function initDirectoryStructure(BuildInitEvent $initEvent)
    {
        $config = $initEvent->getConfig();
        $fullTargetPath = $initEvent->getProjectPath() . '/' . $initEvent->getTargetDirectory();
        // Create or clean the target directory
        if (!$this->fileSystem->exists($fullTargetPath) || !is_dir($fullTargetPath)) {
            $this->fileSystem->mkdir($fullTargetPath);
            $this->verboseInfo(sprintf(
                '<info>The <comment>%s</comment> directory has been created</info>',
                $fullTargetPath
            ));
        } else {
            // If the filename or directoryname starts with dot, we keep it. Eg: .data directory
            $this->fileSystem->remove(Finder::create()->in($fullTargetPath)->depth(0));
            $this->verboseInfo(sprintf(
                '<info>The <comment>%s</comment> directory has been clean</info>',
                $fullTargetPath
            ));
        }

        $dataPath = $config['docker_data_dir'];
        // If it is an relative path
        if (!in_array($dataPath[0], ['/', '~'])) {
            $dataPath = $initEvent->getProjectPath() . '/' . $dataPath;
        }
        if (!$this->fileSystem->exists($dataPath) || !is_dir($dataPath)) {
            $this->fileSystem->mkdir($dataPath);
        }
        $config['docker_data_dir'] = $dataPath;
        $initEvent->setConfig($config);
    }

    /**
     * We can create a recipe with EventSubscriberInterface that can handle events!
     *
     * @param string $projectPath
     * @param string $recipeName
     * @param array  $recipeConfig
     * @param array  $globalConfig
     *
     * @throws \App\Exception\MissingRecipeException
     */
    protected function addRecipeEventSubscribers($projectPath, $recipeName, $recipeConfig, $globalConfig = [])
    {
        $this->verboseInfo(sprintf(
            "\n<info>Register event listeners of <comment>%s</comment> recipe</info>",
            $recipeName
        ));

        /** @var BaseRecipe $recipe */
        $recipe = $this->recipeManager->getRecipe($recipeName);

        if ($recipe instanceof EventSubscriberInterface) {
            $this->eventDispatcher->addSubscriber($recipe);
        }
    }

    /**
     * Build a recipe.
     *
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
        $this->verboseInfo(sprintf(
            "\n<info>Starting build <comment>%s</comment> recipe</info>",
            $recipeName
        ));
        $this->verboseInfo(['config' => $recipeConfig]);

        try {
            /** @var BaseRecipe $recipe */
            $recipe = $this->recipeManager->getRecipe($recipeName);

            /** @var SkeletonFile[] $skeletonFiles */
            $skeletonFiles = $recipe->build($projectPath, $recipeConfig, $globalConfig);

            foreach ($skeletonFiles as $skeletonFile) {
                $fileTarget = $this->getRelativeTargetFilePath($recipeName, $skeletonFile->getFileInfo());
                $fileFullTarget = $projectPath . '/' . $fileTarget;
                // Dump files
                $dumpEvent = new DumpEvent($fileFullTarget, $skeletonFile->getContents());
                $this->eventDispatcher->dispatch(ConfigurationEvents::BEFORE_DUMP, $dumpEvent);
                $this->fileSystem->dumpFile(
                    $dumpEvent->getTargetPath(),
                    $dumpEvent->getContents()
                );
                $this->verboseInfo(sprintf('    <comment>%-40s</comment> # %s', $fileTarget, get_class($skeletonFile)));

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
            $this->verboseInfo(sprintf('<comment>Skip the <options=underscore>%s</> recipe</comment>', $recipeName));
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
     *
     * @return string
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

        // Dump files
        $dumpEvent = new DumpEvent($path, $contents);
        $this->eventDispatcher->dispatch(ConfigurationEvents::BEFORE_DUMP, $dumpEvent);
        $this->fileSystem->dumpFile(
            $dumpEvent->getTargetPath(),
            $dumpEvent->getContents()
        );

        $this->verboseInfo(sprintf('<info>✔ The <comment>%s</comment> makefile has been created!</info>', $path));

        return $path;
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

    /**
     * Print verbose informations. The $info may be array or string.
     *
     * @parameter string|array|null $info
     */
    protected function verboseInfo($info)
    {
        $this->eventDispatcher->dispatch(ConfigurationEvents::VERBOSE_INFO, new VerboseInfoEvent($info));
    }
}
