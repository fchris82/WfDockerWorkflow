<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.10.
 * Time: 16:57
 */

namespace App\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Parser;

class Configuration implements ConfigurationInterface
{
    const ROOT_NODE = 'project';

    /**
     * @var RecipeManager
     */
    protected $recipeManager;

    /**
     * Configuration constructor.
     * @param RecipeManager $recipeManager
     */
    public function __construct(RecipeManager $recipeManager)
    {
        $this->recipeManager = $recipeManager;
    }

    public function loadConfig($configFile, $pwd)
    {
        $ymlParser = new Parser();
        $ymlFilePath = file_exists($configFile) && is_file($configFile)
            ? $configFile
            : $pwd . '/' . $configFile;
        $baseConfig = $ymlParser->parseFile($ymlFilePath);

        $processor = new Processor();
        $fullConfig = $processor->processConfiguration($this, [self::ROOT_NODE => $baseConfig]);

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
                ->scalarNode('version')
                    ->info('<comment>Which WF Makefile version do you want to use?</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
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
                    ->info('<comment>You can add extra <info>makefile files</info>.</comment>')
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

    protected function addRecipesNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('recipes');
        $node->info('<comment>The configs of recipes</comment>');

        /** @var BaseRecipe $recipe */
        foreach ($this->recipeManager->getRecipes() as $recipe) {
            if (!$recipe instanceof HiddenRecipe) {
                $node->append($recipe->getConfig());
            }
        }

        return $node;
    }
}
