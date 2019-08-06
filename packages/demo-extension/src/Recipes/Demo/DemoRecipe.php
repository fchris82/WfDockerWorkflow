<?php declare(strict_types=1);

namespace Wf\DemoExtension\Recipes\Demo;

use Symfony\Component\Yaml\Yaml;
use Wf\DockerWorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Wf\DockerWorkflowBundle\Event\SkeletonBuildBaseEvents;
use Wf\DockerWorkflowBundle\Recipes\BaseRecipe;
use Wf\DockerWorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

class DemoRecipe extends BaseRecipe
{
    const NAME = 'demo_extension';

    /**
     * @var array|string[]
     */
    protected $dockerComposeFiles = [];

    public function getName(): string
    {
        return static::NAME;
    }

    public function getConfig(): NodeDefinition
    {
        $rootNode = parent::getConfig();

        /**
         *  demo_extension:
         *      value: "Hello World!"
         */
        $rootNode
            ->info('<comment>GitLab CI Webtown Runner</comment>')
            ->children()
                ->scalarNode('value')
                    ->info('<comment>Share composer cache or other things between tests. Service name list.</comment>')
                    ->defaultValue('Hello World!')
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    public function registerEventListeners(EventDispatcherInterface $eventDispatcher): void
    {
        $eventDispatcher->addListener(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, [$this, 'collectFiles']);
    }

    public function collectFiles(DumpFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();

        if ($skeletonFile instanceof DockerComposeSkeletonFile) {
            $this->dockerComposeFiles[] = $skeletonFile->getRelativePathname();
        }
    }

    public function getSkeletonVars(string $projectPath, array $recipeConfig, array $globalConfig): array
    {
        return array_merge(parent::getSkeletonVars($projectPath, $recipeConfig, $globalConfig), [
            'services' => $this->parseAllDockerServices($projectPath),
            'value' => $recipeConfig['value'],
        ]);
    }

    /**
     * Find all docker service name through parsing the all included docker-compose.yml file.
     *
     * @return array
     */
    protected function parseAllDockerServices(string $projectPath): array
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
