<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace App\Wizards;

use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardHasAlreadyBuiltException;
use App\Exception\WizardSomethingIsRequiredException;
use App\Wizard\WizardInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class BaseSkeleton.
 */
abstract class BaseWizard implements WizardInterface
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var string
     */
    protected $runCommandsWorkdir;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract public function getDefaultName();

    public function getDefaultGroup()
    {
        return '';
    }

    public function getInfo()
    {
        return '';
    }

    public function isHidden()
    {
        return false;
    }

    /**
     * Beállítjuk az $input-ot. Ez futás alatt változatlan.
     *
     * @param InputInterface $input
     *
     * @return $this
     */
    public function setInput(InputInterface $input)
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Beállítjuk az $output-ot. Ez futás alatt változatlan.
     *
     * @param OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @param Command $command
     *
     * @return $this
     */
    public function setCommand(Command $command)
    {
        $this->command = $command;

        return $this;
    }

    protected function getQuestionHelper()
    {
        if (!$this->questionHelper) {
            $this->questionHelper = $this->command->getHelper('question');
        }

        return $this->questionHelper;
    }

    protected function ask(Question $question)
    {
        return $this->getQuestionHelper()->ask($this->input, $this->output, $question);
    }

    /**
     * runBuild()
     *      ├── initBuild()
     *      │   ├── checkReuires()
     *      │   └── init()
     *      │
     *      ├── build()
     *      │
     *      └── cleanUp()
     *
     * @param $targetProjectDirectory
     *
     * @throws WizardHasAlreadyBuiltException
     *
     * @return string
     */
    public function runBuild($targetProjectDirectory)
    {
        $event = new BuildWizardEvent($targetProjectDirectory);
        $this->initBuild($event);
        $this->build($event);
        $this->cleanUp($event);

        return $targetProjectDirectory;
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws WizardHasAlreadyBuiltException
     */
    protected function initBuild(BuildWizardEvent $event)
    {
        $this->checkRequires($event->getWorkingDirectory());
        if ($this->isBuilt($event->getWorkingDirectory())) {
            throw new WizardHasAlreadyBuiltException($this, $event->getWorkingDirectory());
        }
        $this->init($event);
    }

    protected function init(BuildWizardEvent $event)
    {
        // User function
    }

    abstract protected function build(BuildWizardEvent $event);

    protected function cleanUp(BuildWizardEvent $event)
    {
        // User function
    }

    protected function call($workingDirectory, self $wizard)
    {
        $wizard
            ->setInput($this->input)
            ->setOutput($this->output)
            ->setCommand($this->command);
        try {
            $wizard->checkRequires($workingDirectory);
            if (!$wizard->isBuilt($workingDirectory)) {
                $stepTargetProjectDirectory = $wizard->runBuild($workingDirectory);
            }
        } catch (WizardSomethingIsRequiredException $e) {
            $this->output->writeln($e->getMessage());
        }
    }

    protected function wfIsInitialized($targetProjectDirectory)
    {
        return file_exists($targetProjectDirectory . '/.wf.yml.dist')
            || file_exists($targetProjectDirectory . '/.wf.yml');
    }

    protected function cd($workdir)
    {
        $this->runCommandsWorkdir = $workdir;
    }

    protected function getCmdWorkDir()
    {
        return $this->runCommandsWorkdir ?: $_SERVER['PWD'];
    }

    protected function run($cmd, $workdir = null, $handleReturn = null)
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

        $this->output->writeln(sprintf('[exec] <comment>%s</comment>', $printedCmd));
        $result = $this->liveExecuteCommand($cmd);
        $return = $result['exit_status'];
        $output = $result['output'];

        if (0 === $return) {
            $this->output->writeln(sprintf('[<info>OK</info>] %s', $printedCmd));
        } else {
            $this->output->writeln(sprintf('[<error>ERROR</error> (%d)] %s', $return, $printedCmd));
        }

        if (\is_callable($handleReturn)) {
            return $handleReturn($return, $output);
        }

        return $output;
    }

    protected function runCmdInContainer($cmd, $workdir = null, $handleReturn = null)
    {
        $workdir = $workdir ?: $this->getCmdWorkDir();
        if ($this->wfIsInitialized($workdir)) {
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
                $this->getDockerCmdExtraParameters($workdir),
                $this->getDockerImage(),
                $cmd
            );
        }

        return $this->run(
            $containerCmd,
            $workdir,
            $handleReturn
        );
    }

    protected function liveExecuteCommand($cmd)
    {

        while (@ ob_end_flush()); // end all output buffers if any

        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = "";
        $complete_output = "";

        while (!feof($proc))
        {
            $live_output     = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        // get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        // return exit status and intended output
        return array (
            'exit_status'  => intval($matches[0]),
            'output'       => rtrim(str_replace("Exit status : " . $matches[0], '', $complete_output))
        );
    }

    /**
     * A /package/opt/webtown-workflow/symfony/docker-compose.yml fájlban lehet átadni paramétereket, amik
     * kellhetnek majd generálásoknál. Pl ORIGINAL_PWD .
     *
     * @param string      $name
     * @param null|string $default
     *
     * @return null|string
     */
    protected function getEnv($name, $default = null)
    {
        return array_key_exists($name, $_ENV) ? $_ENV[$name] : $default;
    }

    protected function getDockerCmdExtraParameters($targetProjectDirectory)
    {
        return '';
    }

    protected function getDockerImage()
    {
        return 'fchris82/wf';
    }

    protected function getDockerShell()
    {
        return '/bin/bash';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }

    /**
     * @param string $targetProjectDirectory
     *
     * @return bool
     *
     * @throw WizardSomethingIsRequiredException
     */
    public function checkRequires($targetProjectDirectory)
    {
        return true;
    }
}
