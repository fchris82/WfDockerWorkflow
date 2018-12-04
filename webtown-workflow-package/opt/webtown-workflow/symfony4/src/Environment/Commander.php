<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 16:48
 */

namespace App\Environment;

use App\Exception\CommanderRunException;

class Commander
{
    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    /**
     * @var string
     */
    protected $runCommandsWorkdir;

    /**
     * Commander constructor.
     *
     * @param IoManager           $ioManager
     * @param WfEnvironmentParser $wfEnvironmentParser
     */
    public function __construct(IoManager $ioManager, WfEnvironmentParser $wfEnvironmentParser)
    {
        $this->ioManager = $ioManager;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
    }

    public function cd($workdir)
    {
        $this->runCommandsWorkdir = $workdir;
    }

    protected function getCmdWorkDir()
    {
        return $this->runCommandsWorkdir ?: $_SERVER['PWD'];
    }

    public function run($cmd, $workdir = null)
    {
        $workdir = $workdir ?: $this->getCmdWorkDir();
        $cmd = sprintf('cd %s && %s', $workdir, $cmd);
        $replace = [
            '&&' => '<question>&&</question>',
            '|' => '<question>|</question>',
        ];
        $printedCmd = str_replace(
            array_keys($replace),
            array_values($replace),
            $cmd
        );

        $this->ioManager->writeln(sprintf('[exec] <comment>%s</comment>', $printedCmd));
        $result = $this->liveExecuteCommand($cmd);
        $return = $result['exit_status'];
        $output = $result['output'];

        if (0 === $return) {
            $this->ioManager->writeln(sprintf('[<info>OK</info>] %s', $printedCmd));

            return $output;
        }

        $this->ioManager->writeln(sprintf('[<error>ERROR</error> (%d)] %s', $return, $printedCmd));
        throw new CommanderRunException($cmd, $output, '', $return);
    }

    public function runCmdInContainer($cmd, $image, $extraParameters = '', $workdir = null)
    {
        $workdir = $workdir ?: $this->getCmdWorkDir();
        if ($this->wfEnvironmentParser->wfIsInitialized($workdir)) {
            $containerCmd = sprintf(
                'wf %s',
                $cmd
            );
        } else {
            $environments = [
                'LOCAL_USER_ID'         => '${LOCAL_USER_ID}',
                'LOCAL_USER_NAME'       => '${LOCAL_USER_NAME}',
                'LOCAL_USER_HOME'       => '${LOCAL_USER_HOME}',
                'WF_HOST_TIMEZONE'      => '${WF_HOST_TIMEZONE}',
                'WF_HOST_LOCALE'        => '${WF_HOST_LOCALE}',
                'WF_DOCKER_HOST_CHAIN'  => '"${WF_DOCKER_HOST_CHAIN}$(hostname) "',
                'COMPOSER_HOME'         => '${COMPOSER_HOME}',
                'COMPOSER_MEMORY_LIMIT' => '-1',
                'USER_GROUP'            => '${USER_GROUP}',
                'APP_ENV'               => 'dev',
                'XDEBUG_ENABLED'        => '0',
                'WF_DEBUG'              => '0',
                'CI'                    => '0',
                'DOCKER_RUN'            => '1',
                'WF_TTY'                => '1',
            ];
            $envParameters = [];
            foreach ($environments as $name => $value) {
                $envParameters[] = sprintf('-e %s=%s', $name, $value);
            }

            // Example: `docker run -it -w $(pwd) -v $(pwd):$(pwd) -e TTY=1 -e WF_DEBUG=0 /bin/bash -c "ls -al && php -i"
            $containerCmd = sprintf(
                'docker run -it -u ${LOCAL_USER_ID}:${USER_GROUP} -w %1$s -v ${COMPOSER_HOME}:${COMPOSER_HOME} -v %1$s:%1$s %2$s %3$s %4$s %5$s',
                $workdir,
                implode(' ', $envParameters),
                $extraParameters,
                $image,
                $cmd
            );
        }

        return $this->run(
            $containerCmd,
            $workdir
        );
    }

    protected function liveExecuteCommand($cmd)
    {
        while (@ob_end_flush()); // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = '';
        $complete_output = '';

        while (!feof($proc)) {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return [
            'exit_status'  => (int) ($matches[0]),
            'output'       => rtrim(str_replace('Exit status : ' . $matches[0], '', $complete_output)),
        ];
    }
}
