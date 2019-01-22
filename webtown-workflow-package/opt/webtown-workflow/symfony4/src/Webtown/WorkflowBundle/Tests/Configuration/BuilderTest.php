<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 17:18
 */

namespace App\Webtown\WorkflowBundle\Tests\Configuration;

use App\Webtown\WorkflowBundle\Configuration\Builder;
use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use App\Webtown\WorkflowBundle\Event\ConfigurationEvents;
use App\Webtown\WorkflowBundle\Recipes\CreateBaseRecipe\Recipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Filesystem;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Configurable\ConfigurableRecipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Simple\SimpleRecipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SystemRecipe\SystemRecipe;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Mockery as m;

class BuilderTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testBuildException()
    {
        $eventDispatcher = new EventDispatcher();
        $filesystem = new Filesystem(__DIR__, 'alias');
        $recipeManager = new RecipeManager();
        $builder = new Builder($filesystem, $recipeManager, $eventDispatcher);
        $builder->build([], '', '');
    }

    /**
     * @param array $preSystemRecipes
     * @param array $recipes
     * @param array $postSystemRecipes
     * @param       $config
     * @param       $configHash
     * @param       $result
     *
     * @throws \App\Webtown\WorkflowBundle\Exception\MissingRecipeException
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @dataProvider dpBuild
     */
    public function testBuild($projectPath, array $preSystemRecipes, array $recipes, array $postSystemRecipes, $config, $configHash, $result)
    {
        $eventDispatcher = new EventDispatcher();
        $filesystem = new Filesystem($projectPath, 'alias');
        $recipeManager = new RecipeManager();
        $twigFileLoader = new \Twig_Loader_Filesystem();
        $twigFileLoader->setPaths(__DIR__ . '/../Dummy/Recipes/SystemRecipe', 'AppWebtownWorkflowBundleTestsDummyRecipesSystemRecipeSystemRecipe');
        $twig = new \Twig_Environment($twigFileLoader);
        // Build recipes
        foreach ($preSystemRecipes as $recipeName => $configDefinition) {
            $preSystemRecipe = new SystemRecipe($recipeName, $configDefinition, $twig, $eventDispatcher);
            $recipeManager->addRecipe($preSystemRecipe);
            $eventDispatcher->addListener(
                ConfigurationEvents::REGISTER_EVENT_PREBUILD,
                [$preSystemRecipe, 'onAppConfigurationEventRegisterPrebuild']
            );
        }
        foreach ($postSystemRecipes as $recipeName => $configDefinition) {
            $postSystemRecipe = new SystemRecipe($recipeName, $configDefinition, $twig, $eventDispatcher);
            $recipeManager->addRecipe($postSystemRecipe);
            $eventDispatcher->addListener(
                ConfigurationEvents::REGISTER_EVENT_POSTBUILD,
                [$postSystemRecipe, 'onAppConfigurationEventRegisterPostbuild']
            );
        }

        foreach ($recipes as $recipe) {
            $recipeManager->addRecipe($recipe);
        }
        $builder = new Builder($filesystem, $recipeManager, $eventDispatcher);
        $builder->setTargetDirectoryName('.wf');
        $builder->build($config, $projectPath, $configHash);

        $this->assertEquals($result, $filesystem->getContents());
    }

    public function dpBuild()
    {
        $simpleConfig = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'testproject',
            'imports' => [],
            'docker_data_dir' => '%wf.target_directory%/.data',
        ];
        $testConfig = [
            'version' => [
                'base' => '2.0.0',
                'wf_minimum_version' => '2.1.1',
            ],
            'name' => 'testproject',
            'imports' => [],
            'docker_data_dir' => '%wf.target_directory%/.data',
            'pre' => null,
            'recipes' => [
                'configurable' => ['name' => 'configurable test recipe'],
            ],
            'post' => null,
        ];
        $baseDir = __DIR__ . '/../Resources/ConfigurationBuilder/';
        $preDefinition = new ArrayNodeDefinition('pre');
        $postDefinition = new ArrayNodeDefinition('post');

        $twigEnv = m::mock(\Twig_Environment::class);
        $eventDispatcher = new EventDispatcher();

        return [
            [
                $baseDir . 'empty',     // $targetDirectory
                [],                     // $preSystemRecipes
                [],                     // $recipes
                [],                     // $postSystemRecipes
                $simpleConfig,          // $config
                'testHash',             // $configHash
                [                       // $result
                    'alias/.gitkeep' => '',
                ]
            ],
            [
                $baseDir . 'empty',     // $targetDirectory
                ['pre' => $preDefinition], // $preSystemRecipes
                [new ConfigurableRecipe($twigEnv, $eventDispatcher)],                     // $recipes
                ['post' => $postDefinition],// $postSystemRecipes
                $testConfig,          // $config
                'testHash',             // $configHash
                [                       // $result
                    'alias/.gitkeep' => '',
                    'alias/.wf/pre/.gitkeep' => "testproject\n",
                    'alias/.wf/post/.gitkeep' => "testproject\n",
                ]
            ],
            [
                $baseDir . 'existing',     // $targetDirectory
                [],                     // $preSystemRecipes
                [],                     // $recipes
                [],                     // $postSystemRecipes
                $simpleConfig,          // $config
                'testHash',             // $configHash
                [                       // $result
                    'alias/.gitkeep' => '',
                    'alias/.wf/.data/data.file' => '',
                ]
            ],
            [
                $baseDir . 'existing',     // $targetDirectory
                ['pre' => $preDefinition], // $preSystemRecipes
                [new ConfigurableRecipe($twigEnv, $eventDispatcher)],                     // $recipes
                ['post' => $postDefinition],// $postSystemRecipes
                $testConfig,          // $config
                'testHash',             // $configHash
                [                       // $result
                    'alias/.gitkeep' => '',
                    'alias/.wf/pre/.gitkeep' => "testproject\n",
                    'alias/.wf/post/.gitkeep' => "testproject\n",
                    'alias/.wf/.data/data.file' => '',
                ]
            ],
        ];
    }
}
