<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.25.
 * Time: 15:30
 */

namespace App\Webtown\WorkflowBundle\Tests\Recipes\CreateBaseRecipe;

use App\Webtown\WorkflowBundle\Event\Configuration\BuildInitEvent;
use App\Webtown\WorkflowBundle\Event\ConfigurationEvents;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuildBaseEvents;
use App\Webtown\WorkflowBundle\Skeleton\FileType\DockerComposeSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\ExecutableSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\MakefileSkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Test\Dummy\Filesystem;
use App\Webtown\WorkflowBundle\Tests\Dummy\Configuration\Environment;
use App\Webtown\WorkflowBundle\Recipes\CreateBaseRecipe\Recipe;
use App\Webtown\WorkflowBundle\Tests\SkeletonTestCase;
use Mockery as m;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Yaml\Yaml;

class RecipeTest extends SkeletonTestCase
{
    const BASE_PATH = __DIR__ . '/../../Resources/Recipes/CreateBaseRecipe/';

    public function testGetDirectoryName()
    {
        $recipe = new Recipe(
            m::mock(\Twig_Environment::class),
            m::mock(EventDispatcher::class),
            m::mock(Environment::class)
        );

        $this->assertEquals('', $recipe->getDirectoryName());
    }

    /**
     * @param string                $projectPath
     * @param array                 $env
     * @param array                 $recipeConfig
     * @param BuildInitEvent|null   $buildInitEvent
     * @param array|DumpFileEvent[] $dumpFileEvents
     * @param string                $resultDir
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @dataProvider dpBuild
     */
    public function testBuild(
        string $projectPath,
        array $env,
        array $recipeConfig,
        ?BuildInitEvent $buildInitEvent,
        array $dumpFileEvents,
        string $resultDir
    ) {
        $twig = $this->buildTwig([Recipe::class]);
        $eventDispatcher = new EventDispatcher();
        $environment = new Environment();
        $environment->setEnv($env);
        $recipe = new Recipe($twig, $eventDispatcher, $environment);
        $recipe->registerEventListeners($eventDispatcher);

        $globalConfig = Yaml::parseFile(static::BASE_PATH . $projectPath . '/.wf.yml');

        if ($buildInitEvent) {
            $eventDispatcher->dispatch(ConfigurationEvents::BUILD_INIT, $buildInitEvent);
        }
        foreach ($dumpFileEvents as $dumpFileEvent) {
            $eventDispatcher->dispatch(SkeletonBuildBaseEvents::AFTER_DUMP_FILE, $dumpFileEvent);
        }

        $skeletonFiles = $recipe->build(
            static::BASE_PATH . $projectPath,
            $recipeConfig,
            $globalConfig
        );

        $this->assertSkeletonFilesEquals(
            static::BASE_PATH . $resultDir,
            $skeletonFiles
        );
    }

    public function dpBuild()
    {
        $envContent = file_get_contents(__DIR__ . '/../../../../../../../host/config/env');
        preg_match_all('/^([A-Z_-]+)=(.*)$/m', $envContent, $matches, PREG_SET_ORDER);
        $defaultEnv = [];
        foreach ($matches as $match) {
            $defaultEnv[$match[1]] = $match[2];
        }

        return [
            [
                'in/minimal',
                $defaultEnv,
                [],
                null,
                [],
                'out/minimal',
            ],
            [
                'in/minimal',
                $defaultEnv,
                [],
                new BuildInitEvent([], '', '', 'testASDFG'),
                [
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        SkeletonFile::class,
                        'test1/skeleton.txt',
                        'Skeleton TXT'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        MakefileSkeletonFile::class,
                        'test1/makefile',
                        '# Makefile'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        MakefileSkeletonFile::class,
                        'test2/makefile',
                        '# Makefile'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        DockerComposeSkeletonFile::class,
                        'test2/docker-compose.yml',
                        '# docker compose yml'
                    ), m::mock(Filesystem::class)),
                    new DumpFileEvent($this, $this->buildSkeletonFile(
                        ExecutableSkeletonFile::class,
                        'test3/bin.sh',
                        '# bin.sh'
                    ), m::mock(Filesystem::class)),
                ],
                'out/simple',
            ],
        ];
    }
}
