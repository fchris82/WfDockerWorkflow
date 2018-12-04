<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace App\Recipes\PostBase;

use App\Event\RegisterEventListenersInterface;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\SkeletonBuildBaseEvents;
use App\Recipes\HiddenRecipe;
use App\Skeleton\FileType\DockerComposeSkeletonFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class Recipe
 *
 * After the all
 */
class PostBaseRecipe extends HiddenRecipe implements RegisterEventListenersInterface
{
    const NAME = 'post_base';

    /**
     * @var array|string[]
     */
    protected $dockerComposeFiles = [];

    public function getName()
    {
        return static::NAME;
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, [$this, 'collectFiles']);
    }

    public function collectFiles(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();

        if ($skeletonFile instanceof DockerComposeSkeletonFile) {
            $this->dockerComposeFiles[] = $skeletonFile->getRelativePathname();
        }
    }

    public function getSkeletonVars($projectPath, $recipeConfig, $globalConfig)
    {
        return array_merge(parent::getSkeletonVars($projectPath, $recipeConfig, $globalConfig), [
            'services' => $this->parseAllDockerServices($projectPath),
        ]);
    }

    /**
     * Find all docker service name through parsing the all included docker-compose.yml file.
     *
     * @return array
     */
    protected function parseAllDockerServices($projectPath)
    {
        $services = [];
        foreach ($this->dockerComposeFiles as $dockerComposeFile) {
            $config = Yaml::parse(file_get_contents(
                $projectPath . '/' . $dockerComposeFile
            ));
            if (isset($config['services'])) {
                $services = array_unique(array_merge($services, array_keys($config['services'])));
            }
        }

        return $services;
    }
}
