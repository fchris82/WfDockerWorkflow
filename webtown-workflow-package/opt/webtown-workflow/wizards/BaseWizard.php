<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace Wizards;

use App\Wizard\Helper\ComposerInstaller;
use App\Wizard\WizardInterface;
use App\Exception\WizardHasAlreadyBuiltException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class BaseSkeleton.
 */
abstract class BaseWizard implements WizardInterface
{
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

    abstract public function getDefaultName();

    public function getDefaultGroup()
    {
        return "";
    }

    public function getInfo()
    {
        return "";
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

    public function runBuild($targetProjectDirectory)
    {
        $this->initBuild($targetProjectDirectory);
        $this->build($targetProjectDirectory);
        $this->cleanUp($targetProjectDirectory);

        // @todo (Chris) Megoldani, hogy lehessen target directory-t váltani. Pl ha git clone-ozunk egy alkönyvtárba, akkor a továbbiakban ott fussanak le a dolgok!
    }

    protected function initBuild($targetProjectDirectory)
    {
        $this->checkRequires($targetProjectDirectory);
        if ($this->isBuilt($targetProjectDirectory)) {
            throw new WizardHasAlreadyBuiltException($this, $targetProjectDirectory);
        }
        $this->init($targetProjectDirectory);
    }

    protected function init($targetProjectDirectory)
    {
        // User function
    }

    abstract protected function build($targetProjectDirectory);

    protected function cleanUp($targetProjectDirectory)
    {
        // User function
    }

    protected function run($cmd, $handleReturn = null)
    {
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
        passthru($cmd, $return);
        if ($return == 0) {
            $this->output->writeln(sprintf('[<info>OK</info>] %s', $printedCmd));
        } else {
            $this->output->writeln(sprintf('[<error>ERROR</error> (%d)] %s', $return, $printedCmd));
        }

        if (is_callable($handleReturn)) {
            $handleReturn($return);
        }
    }

    protected function wfIsInitialized($targetProjectDirectory)
    {
        return file_exists($targetProjectDirectory . '/.wf.yml.dist')
            || file_exists($targetProjectDirectory . '/.wf.yml');
    }

    protected function execCmdInDocker($cmd, $targetProjectDirectory, $handleReturn = null)
    {
        if ($this->wfIsInitialized($targetProjectDirectory)) {
            $dockerCmd = sprintf(
                'cd %s && wf %s',
                $targetProjectDirectory,
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
            $dockerCmd = sprintf(
                'docker run -it -u ${LOCAL_USER_ID}:${USER_GROUP} -w %1$s -v ${COMPOSER_HOME}:${COMPOSER_HOME} -v %1$s:%1$s %2$s %3$s %4$s %5$s',
                $targetProjectDirectory,
                implode(' ', $envParameters),
                $this->getDockerCmdExtraParameters($targetProjectDirectory),
                $this->getDockerImage(),
                $cmd
            );
        }

        return $this->run(
            $dockerCmd,
            $handleReturn
        );
    }

    protected function runComposerRequire($targetProjectDirectory, array $packages, array $options = [])
    {
        $packages = trim(implode(' ', $packages));
        if ($packages) {
            $this->output->writeln('<info>Start composer require command ...</info> (' . $packages . ')');
            $this->execCmdInDocker(sprintf(
                'composer require %s %s',
                implode(' ', $options),
                $packages
            ), $targetProjectDirectory);
        }
    }

    public function installComposerPackages($targetProjectDirectory)
    {
        $composerPackages = $this->getRequireComposerPackages();
        if (array_key_exists(ComposerInstaller::COMPOSER_NODEV, $composerPackages)) {
            $this->runComposerRequire($targetProjectDirectory, $composerPackages[ComposerInstaller::COMPOSER_NODEV]);
        }
        if (array_key_exists(ComposerInstaller::COMPOSER_DEV, $composerPackages)) {
            $this->runComposerRequire($targetProjectDirectory, $composerPackages[ComposerInstaller::COMPOSER_DEV], ['--dev']);
        }

        // Reset!
        $this->composerPackages = [];
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

    public function checkRequires($targetProjectDirectory)
    {
        return true;
    }
}
