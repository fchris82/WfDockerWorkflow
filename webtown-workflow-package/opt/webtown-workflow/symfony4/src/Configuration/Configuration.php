<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 16:57
 */

namespace App\Configuration;

use App\Exception\InvalidWfVersionException;
use Recipes\BaseRecipe;
use Recipes\HiddenRecipe;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\Yaml\Parser;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'project';

    /**
     * @var RecipeManager
     */
    protected $recipeManager;

    /**
     * @var Parser
     */
    protected $ymlParser;

    /**
     * @var array|string[]
     */
    protected $importCache = [];

    /**
     * Configuration constructor.
     * @param RecipeManager $recipeManager
     */
    public function __construct(RecipeManager $recipeManager)
    {
        $this->recipeManager = $recipeManager;
    }

    /**
     * @param string      $configFile
     * @param string|null $pwd
     * @param string|null $wfVersion
     *
     * @return array
     *
     * @throws FileLoaderImportCircularReferenceException
     * @throws InvalidWfVersionException
     */
    public function loadConfig($configFile, $pwd = null, $wfVersion = null)
    {
        if (is_null($pwd)) {
            $pwd = dirname($configFile);
        }
        $ymlFilePath = file_exists($configFile) && is_file($configFile)
            ? $configFile
            : $pwd . '/' . $configFile;
        $baseConfig = $this->readConfig($ymlFilePath);

        $processor = new Processor();
        $fullConfig = $processor->processConfiguration($this, [self::ROOT_NODE => $baseConfig]);

        // Check the WF version is correct!
        $this->validateWfVersion($fullConfig, $wfVersion);

        return $fullConfig;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ROOT_NODE);

        $rootNode
            ->children()
                ->arrayNode('imports')
                    ->info('<comment>You can import some other <info>yml</info> files.</comment>')
                    ->example(['.wf.base.yml'])
                    ->scalarPrototype()->end()
                    ->defaultValue([])
                ->end()
                ->arrayNode('version')
                    ->info('<comment>Which WF Makefile version do you want to use? You can combine it with the minimum WF version with the <info>@</info> symbol: <info>[base]@[wf_minimum_version]</info></comment>')
                    ->example('2.0.0@2.198')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('base')
                            ->info('<comment>Which WF Makefile version do you want to use?</comment>')
                            ->example('2.0.0')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()
                        ->scalarNode('wf_minimum_version')
                            ->info('<comment>You can set what is the minimum WF version.</comment>')
                            ->example('2.198')
                            ->defaultNull()
                        ->end()
                    ->end()
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            // @ - the first character - needs to avoid "Notice: Undefined offset: 1" error if the $v contains only base version: "2.0.0"
                            @list($base, $wfMinimumVersion) = explode('@', $v, 2);

                            return [
                                'base' => $base,
                                'wf_minimum_version' => $wfMinimumVersion,
                            ];
                        })
                    ->end()
                ->end()
                // @todo (Chris) Ez elméletileg nem tartalmazhat speciális karaktereket, mert sem a docker, sem a "domain" esetén nem szerencsés, ha tartalmaz olyasmit.
                ->scalarNode('name')
                    ->info('<comment>You have to set a name for the project.</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('docker_data_dir')
                    ->info('<comment>You can set an alternative docker data directory.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('%wf.target_directory%/.data')
                ->end()
                ->arrayNode('makefile')
                    ->info('<comment>You can add extra <info>makefile files</info>. You have to set absolute path, and you can use the <info>%wf.project_path%</info> placeholder or <info>~</info> (your home directory). You can use only these two path!</comment>')
                    ->example('~/dev.mk')
                    ->scalarPrototype()->end()
                ->end()
                ->arrayNode('docker_compose')
                    ->info('<comment>Config the docker compose data.</comment>')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('version')
                            ->info('<comment>You can change the docker compose file version.</comment>')
                            ->cannotBeEmpty()
                            ->defaultValue('3.4')
                        ->end()
                        ->arrayNode('include')
                            ->info('<comment>You can add extra <info>docker-compose.yml files</info>.</comment>')
                            ->example('/home/user/dev.docker-compose.yml')
                            ->scalarPrototype()->end()
                            ->defaultValue([])
                        ->end()
                        ->variableNode('extension')
                            ->info('<comment>Docker Compose yaml configuration. You mustn\'t use the <info>version</info> parameter, it will be automatically.</comment>')
                            ->example([
                                'services' => [
                                    'web' => [
                                        'volumes' => ['~/dev/nginx.conf:/etc/nginx/conf.d/custom.conf'],
                                        'environment' => ['TEST' => '1'],
                                    ],
                                ],
                            ])
                            ->beforeNormalization()
                                ->ifNull()
                                ->thenEmptyArray()
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return !is_array($v);
                                })
                                ->thenInvalid('You have to set array value!')
                            ->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('commands')
                    ->info('<comment>You can add extra <info>commands</info>.</comment>')
                    ->useAttributeAsKey('command')
                    ->variablePrototype()->end()
                ->end()
                ->append($this->addRecipesNode())
            ->end()
        ;

        return $treeBuilder;
    }

    protected function getYmlParser()
    {
        if (!$this->ymlParser) {
            $this->ymlParser = new Parser();
        }

        return $this->ymlParser;
    }

    /**
     * @param string $ymlFilePath
     *
     * @return array
     *
     * @throws FileLoaderImportCircularReferenceException
     */
    protected function readConfig($ymlFilePath)
    {
        $baseConfig = $this->getYmlParser()->parseFile($ymlFilePath);
        $baseConfig = $this->handleImports($baseConfig, $ymlFilePath);

        return $baseConfig;
    }

    /**
     * Check that the current installed WF version is compatibel with this project or you have to upgrade!
     *
     * @param array  $config
     * @param string $wfVersion
     *
     * @throws InvalidWfVersionException
     */
    protected function validateWfVersion($config, $wfVersion)
    {
        $wfMinimumVersion = $config['version']['wf_minimum_version'];
        if ($wfMinimumVersion && version_compare($wfVersion, $wfMinimumVersion, '<')) {
            throw new InvalidWfVersionException(sprintf(
                '<error>You are using the <comment>%s</comment> version of WF, but the program needs at least' .
                ' <comment>%s</comment> version. You have to upgrade the wf with the <comment>wf -u</comment> command!</error>',
                $wfVersion,
                $wfMinimumVersion
            ));
        }
    }

    /**
     * @param array  $baseConfig
     * @param string $baseConfigYmlFullPath
     *
     * @return array
     *
     * @throws FileLoaderImportCircularReferenceException
     */
    protected function handleImports($baseConfig, $baseConfigYmlFullPath)
    {
        $sourceDirectory = dirname($baseConfigYmlFullPath);
        if (array_key_exists('imports', $baseConfig)) {
            // Ebbe gyűjtjük össze az import configokat.
            $fullImportConfig = [];
            foreach ($baseConfig['imports'] as $importYml) {
                if (!file_exists($importYml) || !is_file($importYml)) {
                    $importYmlAlt = $sourceDirectory . '/' . $importYml;
                    if (!file_exists($importYmlAlt) || !is_file($importYmlAlt)) {
                        throw new InvalidConfigurationException(sprintf('The `%s` and `%s` configuration file doesn\'t exist either!', $importYml, $importYmlAlt));
                    }

                    $importYml = $importYmlAlt;
                }

                $importYml = realpath($importYml);
                if (in_array($importYml, $this->importCache)) {
                    $this->importCache[] = $importYml;
                    throw new FileLoaderImportCircularReferenceException($this->importCache);
                }
                $this->importCache[] = $importYml;

                $importConfig = $this->readConfig($importYml);
                // A később importált felülírja a korábbit.
                $fullImportConfig = $this->configDeepMerge($fullImportConfig, $importConfig);

                array_pop($this->importCache);
            }

            // A baseconfig-os felülírja az összes importosat
            $baseConfig = $this->configDeepMerge($fullImportConfig, $baseConfig);
        }

        return $baseConfig;
    }

    protected function configDeepMerge($baseConfig, $overrideConfig)
    {
        foreach ($overrideConfig as $key => $value) {
            if ($this->isConfigLeaf($value) || !array_key_exists($key, $baseConfig)) {
                $baseConfig[$key] = $value;
            } else {
                $baseConfig[$key] = $this->configDeepMerge($baseConfig[$key], $value);
            }
        }

        return $baseConfig;
    }

    protected function isConfigLeaf($value)
    {
        // Not array or empty array
        if (!is_array($value) || $value === []) {
            return true;
        }
        // It is a sequential array, like a list
        if (array_keys($value) === range(0, count($value) - 1)) {
            return $value;
        }

        return false;
    }

    protected function addRecipesNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('recipes');
        $node
            ->info('<comment>The configs of recipes. If you want to disable one from import, set the false value!</comment>')
            ->beforeNormalization()
                ->always(function ($v) {
                    foreach ($v as $service => $value) {
                        if ($value === false) {
                            unset($v[$service]);
                        }
                    }

                    return $v;
                })
            ->end()
        ;

        /** @var BaseRecipe $recipe */
        foreach ($this->recipeManager->getRecipes() as $recipe) {
            if (!$recipe instanceof HiddenRecipe) {
                $node->append($recipe->getConfig());
            }
        }

        return $node;
    }
}
