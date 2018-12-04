<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 17:20
 */

namespace App\Configuration;

use App\Event\Configuration\BuildInitEvent;
use App\Event\Configuration\FinishEvent;
use App\Event\Configuration\VerboseInfoEvent;
use App\Event\ConfigurationEvents;
use App\Event\RegisterEventListenersInterface;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\SkeletonBuildBaseEvents;
use App\Exception\SkipRecipeException;
use App\Recipes\BaseRecipe;
use App\Recipes\HiddenRecipe;
use App\Skeleton\BuilderTrait;
use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Builder
{
    use BuilderTrait;

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
     *
     * @param Filesystem               $fileSystem
     * @param RecipeManager            $recipeManager
     * @param EventDispatcherInterface $eventDispatcher
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
     * @param array  $config
     * @param string $projectPath
     * @param string $configHash
     *
     * @throws \App\Exception\MissingRecipeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function build($config, $projectPath, $configHash)
    {
        if (!$this->targetDirectory) {
            throw new \InvalidArgumentException('You have to call first the `setCachePath` function!');
        }

        // INIT
        $this->initEventListeners($projectPath);
        foreach ($config['recipes'] as $recipeName => $recipeConfig) {
            $this->addRecipeEventListeners($projectPath, $recipeName, $recipeConfig, $config);
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
        foreach ($config['recipes'] as $recipeName => $recipeConfig) {
            $this->buildRecipe($projectPath, $recipeName, $recipeConfig, $config);
        }

        // COMMANDS
        $this->buildRecipe($projectPath, 'commands', [], $config);
        // INCLUDED FILES
        $this->includeExtraFiles($config);
        // DOCKER COMPOSE EXTENSION
        $this->buildRecipe($projectPath, 'docker_compose_extension', [], $config);
        // POST BASE
        $this->buildRecipe($projectPath, 'post_base', [], $config);

        // Finish
        $finishEvent = new FinishEvent($this->fileSystem);
        $this->eventDispatcher->dispatch(ConfigurationEvents::FINISH, $finishEvent);

        $this->buildRecipe($projectPath, '_', [], $config);
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
        if (\is_array($config)) {
            foreach ($config as $key => $value) {
                $config[$key] = $this->configReplaceParameters($value, $parameters);
            }
        } elseif (\is_string($config)) {
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
        if (!\in_array($dataPath[0], ['/', '~'])) {
            $dataPath = $initEvent->getProjectPath() . '/' . $dataPath;
        }
        // The $dataPath would be a symbolic link if you are using deployer. If it is a symbolic link, it can link to
        // "outside from docker" and it causes error! That's why you have to check, if it is an existing symbolic link,
        // then you have to skip the `mkdir` command!
        //
        // Host filestructure:
        // -------------------
        //
        //  [project_path]
        //      ├── current -> releases/28
        //      ├── release -> releases/29
        //      ├── releases
        //      │   ├── 28
        //      │   │   └── [...]
        //      │   │
        //      │   └── 29
        //      │       ├── .wf
        //      │       │   └── .data -> ../../../shared/.wf/.data
        //      │       │
        //      │       └── [...]
        //      │
        //      └── shared
        //          ├── .wf
        //          │   └── .data       <- Link to here, it is missing in docker!
        //          │       └── [...]
        //          │
        //          └── [...]
        //
        // Container filestructure:
        // -------------------
        //
        //  [project_path]
        //      ├── .wf
        //      │   └── .data -> ../../../shared/.wf/.data  <== Missing link target!
        //      │
        //      └── [...]
        //
        if ((!$this->fileSystem->exists($dataPath) || !is_dir($dataPath)) && !is_link($dataPath)) {
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
    protected function addRecipeEventListeners($projectPath, $recipeName, $recipeConfig, $globalConfig = [])
    {
        $this->verboseInfo(sprintf(
            "\n<info>Register event listeners of <comment>%s</comment> recipe</info>",
            $recipeName
        ));

        /** @var BaseRecipe $recipe */
        $recipe = $this->recipeManager->getRecipe($recipeName);

        if ($recipe instanceof RegisterEventListenersInterface) {
            $recipe->registerEventListeners($this->eventDispatcher);
        }
    }

    /**
     * Build a recipe.
     *
     * @param string $projectPath
     * @param string $recipeName
     * @param array  $recipeConfig
     * @param array  $globalConfig
     *
     * @throws \App\Exception\MissingRecipeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
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
            $this->fixFilePath($projectPath, $recipe, $skeletonFiles);

            $this->dumpSkeletonFiles($skeletonFiles);
        } catch (SkipRecipeException $e) {
            // do nothing
            $this->verboseInfo(sprintf('<comment>Skip the <options=underscore>%s</> recipe</comment>', $recipeName));
        }
    }

    /**
     * @param $projectPath
     * @param BaseRecipe     $recipe
     * @param SkeletonFile[] $skeletonFiles
     */
    protected function fixFilePath($projectPath, BaseRecipe $recipe, $skeletonFiles)
    {
        foreach ($skeletonFiles as $skeletonFile) {
            $relativeTargetPath = sprintf(
                implode(\DIRECTORY_SEPARATOR, ['%s', '%s', '%s']),
                $this->targetDirectory,
                $recipe->getDirectoryName(),
                $skeletonFile->getRelativePath()
            );
            $skeletonFile->setRelativePath($relativeTargetPath);
            $skeletonFile->move($projectPath);
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
     * Print verbose informations. The $info may be array or string.
     *
     * @parameter string|array|null $info
     */
    protected function verboseInfo($info)
    {
        $this->eventDispatcher->dispatch(ConfigurationEvents::VERBOSE_INFO, new VerboseInfoEvent($info));
    }

    /**
     * @param $projectPath
     *
     * @throws \App\Exception\MissingRecipeException
     */
    protected function initEventListeners($projectPath)
    {
        $this->eventDispatcher->addListener(
            SkeletonBuildBaseEvents::AFTER_DUMP_FILE,
            [$this, 'fileVerboseInfo'],
            -999
        );
        // Register hidden recipe listeners
        $recipes = $this->recipeManager->getRecipes();
        foreach ($recipes as $recipe) {
            if ($recipe instanceof HiddenRecipe) {
                $this->addRecipeEventListeners($projectPath, $recipe->getName(), []);
            }
        }
    }

    public function fileVerboseInfo(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        $this->verboseInfo(sprintf('    <comment>%-40s</comment> # %s', $skeletonFile->getRelativePath(), \get_class($skeletonFile)));
    }

    protected function eventBeforeDumpFile(DumpFileEvent $event)
    {
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
    }

    protected function eventAfterDumpFile(DumpFileEvent $event)
    {
    }

    protected function eventSkipDumpFile(DumpFileEvent $event)
    {
    }
}
