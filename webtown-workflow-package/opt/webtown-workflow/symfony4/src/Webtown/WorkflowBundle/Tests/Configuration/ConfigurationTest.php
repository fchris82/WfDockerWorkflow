<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 9:54
 */

namespace App\Webtown\WorkflowBundle\Tests\Configuration;

use App\Webtown\WorkflowBundle\Configuration\Configuration;
use App\Webtown\WorkflowBundle\Configuration\RecipeManager;
use App\Webtown\WorkflowBundle\Exception\InvalidWfVersionException;
use App\Webtown\WorkflowBundle\Tests\Dummy\Filesystem;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Configurable\ConfigurableRecipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Hidden\HiddenRecipe;
use App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Simple\SimpleRecipe;
use App\Webtown\WorkflowBundle\Tests\TestCase;
use Mockery as m;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigurationTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /**
     * @param $base
     * @param $new
     * @param $result
     *
     * @throws \ReflectionException
     *
     * @dataProvider getConfigurations
     */
    public function testConfigDeepMerge($base, $new, $result)
    {
        $configuration = new Configuration(new RecipeManager(), m::mock(Filesystem::class));
        $response = $this->getMethod($configuration, 'configDeepMerge')->invokeArgs($configuration, [$base, $new]);

        $this->assertEquals($result, $response);
    }

    public function getConfigurations()
    {
//        return [
//            # 6
//            [
//                ['test' => ['subvalue' => 1, 'other' => 2]],
//                ['test' => ['subvalue' => 2]],
//                ['test' => ['subvalue' => 2, 'other' => 2]],
//            ],
//        ];
        return [
            # 0
            [
                [],
                [],
                [],
            ],
            # 1
            [
                ['test' => 1],
                [],
                ['test' => 1],
            ],
            # 2
            [
                [],
                ['test' => 2],
                ['test' => 2],
            ],
            # 3
            [
                ['test' => 1],
                ['test' => 2],
                ['test' => 2],
            ],
            # 4
            [
                ['test' => 1],
                ['test' => null],
                ['test' => null],
            ],
            # 5
            [
                ['test' => ['subvalue' => 1]],
                ['test' => null],
                ['test' => null],
            ],
            # 6
            [
                ['test' => ['subvalue' => 1, 'other' => 2]],
                ['test' => ['subvalue' => 2]],
                ['test' => ['subvalue' => 2, 'other' => 2]],
            ],
            # 7
            [
                ['test1' => 1],
                ['test2' => 2],
                [
                    'test1' => 1,
                    'test2' => 2,
                ],
            ],
            # 8
            [
                ['test' => ['subvalue' => 1, 'other' => 2]],
                ['test' => ['other2' => 2]],
                ['test' => ['subvalue' => 1, 'other' => 2, 'other2' => 2]],
            ],
            # 9
            [
                ['test' => ['subvalue' => ['test1', 'test1', 'test1']]],
                ['test' => ['subvalue' => ['test2']]],
                ['test' => ['subvalue' => ['test2']]],
            ],
        ];
    }

    /**
     * @param string $directory
     * @param string $file
     * @param bool|\Exception   $exception
     *
     * @throws \App\Webtown\WorkflowBundle\Exception\InvalidWfVersionException
     * @throws \Symfony\Component\Config\Exception\FileLoaderImportCircularReferenceException
     *
     * @dataProvider getLoads
     */
    public function testLoadConfig(string $directory, string $file = '.wf.yml', $exception = false)
    {
        if ($exception instanceof \Exception) {
            $this->expectException(get_class($exception));
        }

        $workingDirectory = realpath(__DIR__ . '/../Resources/Configuration/' . $directory);
        $filesystem = new Filesystem($workingDirectory);
        $configuration = new Configuration($this->buildRecipeManager(), $filesystem);

        $fullConfig = $configuration->loadConfig(
            $workingDirectory . DIRECTORY_SEPARATOR . $file,
            null,
            '2.1.1'
        );
        if (!$exception instanceof \Exception) {
            $result = Yaml::parseFile($workingDirectory . '/result.yml');
            $this->assertEquals($result, $fullConfig);
        }
    }

    public function getLoads()
    {
        return [
            ['full_base'],
            ['minimal', '.wf.v1.yml'],
            ['minimal', '.wf.v2.yml'],
            ['imperfect', '.wf.missing_name.yml', new InvalidConfigurationException()],
            ['imperfect', '.wf.missing_version1.yml', new InvalidConfigurationException()],
            ['imperfect', '.wf.missing_version2.yml', new InvalidConfigurationException()],
            ['invalid', '.wf.invalid_wf_version.yml', new InvalidWfVersionException()],
            ['invalid', '.wf.missing_import_file.yml', new InvalidConfigurationException()],
            ['invalid', '.wf.circular_reference_import.v1.yml', new FileLoaderImportCircularReferenceException(['.wf.circular_reference_import.v1.yml'])],
            ['recipes', '.wf.mixed.yml'],
            ['recipes', '.wf.missing_required.yml', new InvalidConfigurationException()],
            ['recipes', '.wf.unknown_recipe.yml', new InvalidConfigurationException()],
        ];
    }

    protected function buildRecipeManager()
    {
        $twigEnv = m::mock(\Twig_Environment::class);
        $eventDispatcher = m::mock(EventDispatcherInterface::class);
        $manager = new RecipeManager();
        $manager->addRecipe(new HiddenRecipe($twigEnv, $eventDispatcher));
        $manager->addRecipe(new SimpleRecipe($twigEnv, $eventDispatcher));
        $manager->addRecipe(new ConfigurableRecipe($twigEnv, $eventDispatcher));

        return $manager;
    }
}
