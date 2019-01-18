<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 13:52
 */

namespace App\Webtown\WorkflowBundle\Tests\Recipes;

use App\Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Hidden\HiddenRecipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Simple\SimpleRecipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkeletonParent\SimpleSkeletonParent;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

class BaseRecipeTest extends TestCase
{
    public function testGetConfig()
    {
        $recipe = new SimpleRecipe(new \Twig_Environment(new \Twig_Loader_Filesystem()), new EventDispatcher());
        $dumper = new YamlReferenceDumper();
        $rootNode = $recipe->getConfig();
        $ymlTree = $dumper->dumpNode($rootNode->getNode(true));
        $config = Yaml::parse($ymlTree);

        $this->assertEquals(['simple' => []], $config);
    }

    /**
     * @param string       $projectPath
     * @param array|string $recipeConfig
     * @param array        $globalConfig
     * @param array        $result
     *
     * @dataProvider dpGetSkeletonVars
     */
    public function testGetSkeletonVars(string $projectPath, $recipeConfig, array $globalConfig, array $result)
    {
        $recipe = new SimpleRecipe(new \Twig_Environment(new \Twig_Loader_Filesystem()), new EventDispatcher());
        $response = $recipe->getSkeletonVars($projectPath, $recipeConfig, $globalConfig);

        $this->assertEquals($result, $response);
    }

    public function dpGetSkeletonVars()
    {
        return [
            // Simple 1
            ['', [], [], [
                'config' => [],
                'project_path' => '',
                'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/simple',
                'env' => $_ENV,
            ]],
            // Simple 2
            ['project_path', ['config_key' => 'value'], ['global' => true], [
                'config' => ['global' => true],
                'project_path' => 'project_path',
                'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/simple',
                'env' => $_ENV,
                'config_key' => 'value',
            ]],
            // String config
            ['', 'string config value', [], [
                'config' => [],
                'project_path' => '',
                'recipe_path' => '${BASE_DIRECTORY}/${PROJECT_DIR_NAME}/${WF_TARGET_DIRECTORY}/simple',
                'env' => $_ENV,
                'value' => 'string config value',
            ]],
        ];
    }

    /**
     * @param array  $parents
     * @param string $projectPath
     * @param array  $recipeConfig
     * @param array  $globalConfig
     * @param array  $result
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @dataProvider dpBuild
     */
    public function testBuild(array $parents, string $projectPath, array $recipeConfig, array $globalConfig, array $result)
    {
        $twigLoader = new \Twig_Loader_Filesystem();
        $twigLoader->setPaths(
            [realpath(__DIR__ . '/../Dummy/Recipes/Simple')],
            'AppWebtownWorkflowBundleTestsDummyRecipesSimpleSimpleRecipe'
        );
        $twigLoader->setPaths(
            [realpath(__DIR__ . '/../Dummy/Recipes/SimpleSkeletonParent')],
            'AppWebtownWorkflowBundleTestsDummyRecipesSimpleSkeletonParentSimpleSkeletonParent'
        );
        $recipe = new SimpleRecipe(new \Twig_Environment($twigLoader), new EventDispatcher());
        SimpleRecipe::setSkeletonParents($parents);
        $response = $recipe->build($projectPath, $recipeConfig, $globalConfig);

        $files = [];
        foreach ($response as $skeletonFile) {
            $files[$skeletonFile->getRelativePathname()] = get_class($skeletonFile);
        }

        ksort($result);
        ksort($files);
        $this->assertEquals($result, $files);
    }

    public function dpBuild()
    {
        return [
            // Without parent
            [[], '', [], [], [
                'docker-compose.yml' => DockerComposeSkeletonFile::class,
                'executable.sh' => ExecutableSkeletonFile::class,
                'makefile' => MakefileSkeletonFile::class,
                'simple.file' => SkeletonFile::class,
            ]],
            // With parent
            [[SimpleSkeletonParent::class], '', [], [], [
                'docker-compose.yml' => DockerComposeSkeletonFile::class,
                'docker-compose.second.yml' => DockerComposeSkeletonFile::class,
                'executable.sh' => ExecutableSkeletonFile::class,
                'makefile' => MakefileSkeletonFile::class,
                'non-executable.sh' => ExecutableSkeletonFile::class,
                'simple.file' => SkeletonFile::class,
            ]],
        ];
    }

    /**
     * @param string $pattern
     * @param array  $items
     * @param string $result
     *
     * @dataProvider dpMakefileMultilineFormatter
     */
    public function testMakefileMultilineFormatter(string $pattern, array $items, string $result)
    {
        $recipe = new SimpleRecipe(new \Twig_Environment(new \Twig_Loader_Filesystem()), new EventDispatcher());
        $response = $recipe->makefileFormat($pattern, $items);

        $this->assertEquals($result, $response);
    }

    public function dpMakefileMultilineFormatter()
    {
        return [
            ['', [], ''],
            ['include %s', ['1.mk'], 'include 1.mk'],
            [
                'include %s',
                ['1.mk', '2.mk', '3.mk', '4.mk'],
                // Backslash + \n!!
                "include 1.mk \\\n" .
                "        2.mk \\\n" .
                "        3.mk \\\n" .
                "        4.mk"
            ],
            [
                'FOO := %s',
                ['value1', 'value2', 'value3'],
                // Backslash + \n!!
                "FOO := value1 \\\n" .
                "       value2 \\\n" .
                "       value3"
            ],
        ];
    }
}
