<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace App\Webtown\WfConfigEditorBundle\ConfigEditorExtension;

use App\Webtown\WfConfigEditorBundle\DefinitionDumper\ArrayDumper;
use App\Webtown\WorkflowBundle\Configuration\Configuration;
use App\Webtown\WorkflowBundle\Recipes\SystemRecipe;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ConfigEditorExtensionRecipe
 *
 * You have to exclude the server directory from class autoloader:
 *  App\:
 *      resource: ../src
 *      exclude:
 *          - '../src/Webtown/WfBaseSystemRecipesBundle/SystemRecipes/ConfigEditorExtension/server'
 */
class ConfigEditorExtensionRecipe extends SystemRecipe
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

    public function __construct(Configuration $configuration, ArrayDumper $jsonDumper, \Twig_Environment $twig, EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct($twig, $eventDispatcher);
        $this->configuration = $configuration;
        $this->arrayDumper = $jsonDumper;
    }

    public function getName()
    {
        return static::NAME;
    }

    public function getSkeletonVars($projectPath, $recipeConfig, $globalConfig)
    {
        $baseConfig = parent::getSkeletonVars($projectPath, $recipeConfig, $globalConfig);

        return array_merge([
            'doc_root' => __DIR__ . DIRECTORY_SEPARATOR . 'server',
            'full_config_array' => $this->getConfigurationArray(),
        ], $baseConfig);
    }

    protected function getConfigurationArray()
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
}
