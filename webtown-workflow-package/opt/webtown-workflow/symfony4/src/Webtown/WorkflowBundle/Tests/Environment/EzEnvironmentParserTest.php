<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.27.
 * Time: 14:58
 */

namespace App\Webtown\WorkflowBundle\Tests\Environment;

use App\Webtown\WorkflowBundle\Environment\EzEnvironmentParser;
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use App\Webtown\WorkflowBundle\Tests\Dummy\Filesystem;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class EzEnvironmentParserTest extends TestCase
{
    /**
     * @param string          $directory
     * @param bool|\Exception $result
     *
     * @dataProvider getProjects
     */
    public function testIsEzProject($directory, $result, $removeFiles = [])
    {
        $ioManager = m::mock(IoManager::class);
        $workingDirectory = __DIR__ . '/../Resources/Environment/' . $directory;
        $filesystem = new Filesystem($workingDirectory);
        $composerParser = new ComposerInstalledVersionParser($filesystem);
        $ezParser = new EzEnvironmentParser($ioManager, $composerParser);

        foreach ($removeFiles as $file) {
            $filesystem->remove($workingDirectory . '/' . $file);
        }

        if ($result instanceof \Exception) {
            $this->expectException(\get_class($result));
        }

        $response = $ezParser->isEzProject($workingDirectory);
        if (!$result instanceof \Exception) {
            $this->assertEquals($result, $response);
        }
    }

    public function getProjects()
    {
        return [
            ['env_empty', false],
            ['env_no_composer', false],
            ['env_composer_no_sf', false],
            ['env_composer_no_sf_only_json', false],
            ['env_composer_sf2', false],
            ['env_composer_sf3', false],
            ['env_composer_sf4', false],
            ['env_composer_ez1', true],
            ['env_composer_ez1', true, ['.ez.yml']],
            ['env_composer_ez2', true],
            ['env_composer_ez_invalid_version', true],
        ];
    }

    // @todo
//    public function testGetSymfonyEnvironmentVariables()
//    {
//
//    }
}