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
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Environment\WfEnvironmentParser;
use App\Webtown\WorkflowBundle\Exception\CommanderRunException;
use Symfony\Component\Filesystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class CommanderTest extends TestCase
{
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
        $ioManager = new IoManagerMock();
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

//    public function testRunCmdInContainer()
//    {
// @todo
//    }
}

class IoManagerMock extends IoManager
{
    /**
     * @var array
     */
    protected $outputLog = [];

    public function writeln($text)
    {
        $this->outputLog[] = $text;
    }

    public function getLog()
    {
        return $this->outputLog;
    }

    public function getLogAsString()
    {
        return implode("\n", $this->outputLog);
    }
}
