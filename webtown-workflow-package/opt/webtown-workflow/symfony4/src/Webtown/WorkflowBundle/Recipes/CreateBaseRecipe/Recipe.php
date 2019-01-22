<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.27.
 * Time: 13:56
 */

namespace App\Webtown\WorkflowBundle\Recipes\CreateBaseRecipe;

use App\Webtown\WorkflowBundle\Configuration\Builder;
use App\Webtown\WorkflowBundle\Configuration\Environment;
use App\Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use App\Webtown\WorkflowBundle\Event\ConfigurationEvents;
use App\Webtown\WorkflowBundle\Event\RegisterEventListenersInterface;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use App\Webtown\WorkflowBundle\Recipes\SystemRecipe;
use App\Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Recipe extends SystemRecipe implements RegisterEventListenersInterface
{
    const NAME = '_';

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * @var string
     */
    protected $makefileName;

    /**
     * @var array|string[]
     */
    protected $makefiles = [];

    /**
     * @var array|string[]
     */
    protected $dockerComposeFiles = [];

    /**
     * Recipe constructor.
     *
     * @param \Twig_Environment $twig
     * @param Environment       $environment
     */
    public function __construct(\Twig_Environment $twig, EventDispatcherInterface $eventDispatcher, Environment $environment)
    {
        parent::__construct($twig, $eventDispatcher);
        $this->environment = $environment;
    }

    public function getName()
    {
        return static::NAME;
    }

    /**
     * @param string $targetPath
     * @param array  $recipeConfig Here it is the `$globalConfig`
     * @param array  $globalConfig
     *
     * @return array
     *
     * @see Builder::build()
     */
    public function getSkeletonVars($targetPath, $recipeConfig, $globalConfig)
    {
        $dockerComposeFiles = array_map(function ($v) {
            // If the path start with `/` or `~` we won't change, else we put the project path before it
            return \in_array($v[0], ['/', '~']) ? $v : '$(PROJECT_WORKING_DIRECTORY)/' . $v;
        }, $this->dockerComposeFiles);

        return array_merge(parent::getSkeletonVars($targetPath, $recipeConfig, $globalConfig), [
            'wf_target_directory'   => $this->environment->getConfigValue(Environment::CONFIG_WORKING_DIRECTORY),
            'wf_config_file'        => $this->environment->getConfigValue(Environment::CONFIG_CONFIGURATION_FILE),
            'wf_env_file'           => $this->environment->getConfigValue(Environment::CONFIG_ENV_FILE),
            'include_makefiles'     => $this->makefileMultilineFormatter('include %s', $this->makefiles),
            'docker_compose_files'  => $this->makefileMultilineFormatter('DOCKER_CONFIG_FILES := %s', $dockerComposeFiles),
        ]);
    }

    public function init(BuildInitEvent $event)
    {
        $this->makefileName = $event->getConfigHash() . '.mk';
    }

    public function collectFiles(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();

        switch (true) {
            case $skeletonFile instanceof MakefileSkeletonFile:
                $this->makefiles[] = $skeletonFile->getRelativePathname();
                break;
            case $skeletonFile instanceof DockerComposeSkeletonFile:
                $this->dockerComposeFiles[] = $skeletonFile->getRelativePathname();
                break;
        }
    }

    protected function renameMakefile(PostBuildSkeletonFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        if ('makefile' == $skeletonFile->getFileName()) {
            $skeletonFile->setFileName($this->makefileName);
        }
    }

    public function getDirectoryName()
    {
        return '';
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(ConfigurationEvents::BUILD_INIT, [$this, 'init']);
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, [$this, 'collectFiles']);
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent)
    {
        $this->renameMakefile($postBuildSkeletonFileEvent);
        parent::eventAfterBuildFile($postBuildSkeletonFileEvent);
    }
}