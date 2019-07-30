<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace App\Webtown\WfConfigEditorBundle\ConfigEditorExtension;

use App\Webtown\WfConfigEditorBundle\DefinitionDumper\ArrayDumper;
use App\Webtown\WorkflowBundle\Configuration\Configuration;
use App\Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use App\Webtown\WorkflowBundle\Event\ConfigurationEvents;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use App\Webtown\WorkflowBundle\Recipes\SystemRecipe;
use App\Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

/**
 * Class ConfigEditorExtensionRecipe
 *
 * You have to exclude the server directory from class autoloader:
 *  App\:
 *      resource: ../src
 *      exclude:
 *          - '../src/Webtown/WfBaseSystemRecipesBundle/SystemRecipes/ConfigEditorExtension/server'
 */
class ConfigEditorExtensionRecipe extends SystemRecipe implements EventSubscriberInterface
{
    const NAME = 'config_editor';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var ArrayDumper
     */
    protected $arrayDumper;

    /**
     * @var array
     */
    protected $availableParameters = [];

    /**
     * @var array
     */
    protected $dockerComposeFiles = [];

    public function __construct(
        Configuration $configuration,
        ArrayDumper $jsonDumper,
        Environment $twig,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($twig, $eventDispatcher);
        $this->configuration = $configuration;
        $this->arrayDumper = $jsonDumper;
    }

    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigurationEvents::BUILD_INIT => ['registerAvailableParameters', -99],
            SkeletonBuildBaseEvents::AFTER_DUMP_FILE => ['collectDockerFiles', -99],
        ];
    }

    public function registerAvailableParameters(BuildInitEvent $event): void
    {
        $this->availableParameters = $event->getParameters();
    }

    /**
     * Register docker-compose file to collect docker-compose service names.
     *
     * @param DumpFileEvent $event
     */
    public function collectDockerFiles(DumpFileEvent $event): void
    {
        $skeletonFile = $event->getSkeletonFile();

        if ($skeletonFile instanceof DockerComposeSkeletonFile) {
            $this->dockerComposeFiles[$skeletonFile->getRelativePathname()] = $skeletonFile->getRelativePathname();
        }
    }

    public function getSkeletonVars(string $projectPath, array $recipeConfig, array $globalConfig): array
    {
        $baseConfig = parent::getSkeletonVars($projectPath, $recipeConfig, $globalConfig);

        return array_merge([
            'doc_root' => __DIR__ . \DIRECTORY_SEPARATOR . 'server',
            'full_config_array' => $this->getConfigurationArray(),
            'available_placeholders' => array_keys($this->availableParameters),
            'docker_compose_services' => $this->getDockerComposeServices(),
        ], $baseConfig);
    }

    protected function getConfigurationArray(): array
    {
        /** @var ArrayNode $rootNode */
        $rootNode = $this->configuration->getConfigTreeBuilder()->buildTree();
        $configs = [];
        // Show only the children
        foreach ($rootNode->getChildren() as $name => $node) {
            $configs[$name] = $this->arrayDumper->dumpNode($node);
        }

        return ['children' => $configs];
    }

    protected function getDockerComposeServices(): array
    {
        $services = [];
        foreach ($this->dockerComposeFiles as $dockerComposeFilePath) {
            $config = Yaml::parse(file_get_contents($dockerComposeFilePath));
            if (\array_key_exists('services', $config)) {
                $services = array_merge($services, array_keys($config['services']));
            }
        }

        return array_values(array_unique($services));
    }
}
