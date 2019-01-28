<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.24.
 * Time: 12:09
 */

namespace App\Webtown\WorkflowBundle\Tests\Environment;

use App\Webtown\WorkflowBundle\Configuration\Configuration;
use App\Webtown\WorkflowBundle\Environment\Commander;
use App\Webtown\WorkflowBundle\Tests\Dummy\Environment\IoManager;
use App\Webtown\WorkflowBundle\Environment\WfEnvironmentParser;
use App\Webtown\WorkflowBundle\Exception\CommanderRunException;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class CommanderTest extends TestCase
{
    const TEST_DOCKER_IMAGE = 'fchris82/wf';

    public function tearDown()
    {
        m::close();
    }

    /**
     * @param string            $workdir
     * @param string            $cmd
     * @param array             $ioOutput
     * @param string|\Exception $result
     *
     * @throws CommanderRunException
     *
     * @dataProvider dpRun
     */
    public function testRun(string $workdir, string $cmd, array $ioOutput, $result)
    {
        $ioManager = new IoManager();
        $wfEnvironmentParser = new WfEnvironmentParser(
            m::mock(Configuration::class),
            m::mock(Filesystem::class)
        );
        $commander = new Commander($ioManager, $wfEnvironmentParser);
        $commander->setLiveEcho(false);

        if ($result instanceof \Exception) {
            $this->expectException(get_class($result));
        }

        $commander->cd($workdir);
        try {
            $output = $commander->run($cmd);
            $this->assertEquals($ioOutput, $ioManager->getLog());
            $this->assertEquals(trim($result), trim($output));
        } catch (CommanderRunException $e) {
            $this->assertEquals($ioOutput, $ioManager->getLog());
            throw $e;
        }
    }

    public function dpRun()
    {
        return [
            [__DIR__, 'ls', [
                '[exec] <comment>cd ' . __DIR__ . ' <question>&&</question> ls</comment>',
                '[<info>OK</info>] cd ' . __DIR__ . ' <question>&&</question> ls'
            ], "CommanderTest.php\nEzEnvironmentParserTest.php\nMicroParser\nSymfonyEnvironmentParserTest.php\nWfEnvironmentParserTest.php"],
            [__DIR__, 'ls -e', [
                '[exec] <comment>cd ' . __DIR__ . ' <question>&&</question> ls -e</comment>',
                '[<error>ERROR</error> (2)] cd ' . __DIR__ . ' <question>&&</question> ls -e',
            ], new CommanderRunException('ls -e', '')],
        ];
    }

    /**
     * @param string $workdir
     * @param string $cmd
     * @param array  $ioOutput
     * @param        $result
     *
     * @throws CommanderRunException
     *
     * @dataProvider dpRunCmdInContainer
     */
    public function testRunCmdInContainer(string $workdir, string $cmd, array $ioOutput, $result)
    {
        $ioManager = new IoManager();
        $wfEnvironmentParser = m::mock(WfEnvironmentParser::class, [
            'wfIsInitialized' => false,
        ]);
        $commander = new Commander($ioManager, $wfEnvironmentParser);
        $commander->setLiveEcho(false);

        if ($result instanceof \Exception) {
            $this->expectException(get_class($result));
        }

        try {
            $output = $commander->runCmdInContainer(
                $cmd,
                self::TEST_DOCKER_IMAGE,
                sprintf('-e %s=%s', 'COMMANDER_TEST', 'test'),
                $workdir
            );
            $this->assertEquals(array_map(function ($v) {
                return substr($v, 0, 30);
            }, $ioOutput), array_map(function ($v) {
                return substr($v, 0, 30);
            }, $ioManager->getLog()));
            // We call a "Docker In Docker", so the __DIR__ gets a missing directory from the host. We use the ${HOME},
            // that gets a correct directory, but we don't know the contents, so we don't compare it.
            //$this->assertEquals(trim($result), trim($output));
        } catch (CommanderRunException $e) {
            $this->assertEquals(array_map(function ($v) {
                return substr($v, 0, 30);
            }, $ioOutput), array_map(function ($v) {
                return substr($v, 0, 30);
            }, $ioManager->getLog()));

            throw $e;
        }
    }

    public function dpRunCmdInContainer()
    {
        return [
            ['${HOME}', 'ls', [
                '[exec] <comment>cd ${HOME} <question>&&</question> ls</comment>',
                '[<info>OK</info>] cd ${HOME} <question>&&</question> ls'
            ], ''],
            ['${HOME}', 'ls -e', [
                '[exec] <comment>cd ${HOME} <question>&&</question> ls -e</comment>',
                '[<error>ERROR</error> (2)] cd ' . __DIR__ . ' <question>&&</question> ls -e',
            ], new CommanderRunException('ls -e', '')],
        ];
    }
}
