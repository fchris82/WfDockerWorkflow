<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:55
 */

namespace App\Wizards\Deployer;

use App\Environment\Commander;
use App\Environment\EnvParser;
use App\Environment\EzEnvironmentParser;
use App\Environment\IoManager;
use App\Environment\MicroParser\ComposerInstalledVersionParser;
use App\Environment\WfEnvironmentParser;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\CommanderRunException;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Skeleton\FileType\SkeletonFile;
use App\Wizards\BaseSkeletonWizard;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeployerWizard extends BaseSkeletonWizard
{
    /**
     * @var ComposerInstalledVersionParser
     */
    protected $composerInstalledVersionParser;

    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    /**
     * @var EzEnvironmentParser
     */
    protected $ezEnvironmentParser;

    /**
     * @var EnvParser
     */
    protected $envParser;

    public function __construct(
        ComposerInstalledVersionParser $composerInstalledVersionParser,
        WfEnvironmentParser $wfEnvironmentParser,
        EzEnvironmentParser $ezEnvironmentParser,
        EnvParser $envParser,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->composerInstalledVersionParser = $composerInstalledVersionParser;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
        $this->ezEnvironmentParser = $ezEnvironmentParser;
        $this->envParser = $envParser;
    }

    public function getDefaultName()
    {
        return 'Deployer';
    }

    public function getInfo()
    {
        return 'Add Deployer';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    /**
     * @param $targetProjectDirectory
     *
     * @throws WizardSomethingIsRequiredException
     * @throws WizardWfIsRequiredException
     *
     * @return bool
     */
    public function checkRequires($targetProjectDirectory)
    {
        if (!file_exists($targetProjectDirectory . '/composer.json')) {
            throw new WizardSomethingIsRequiredException(sprintf('Initialized composer is required for this!'));
        }
        if (!$this->wfEnvironmentParser->wfIsInitialized($targetProjectDirectory)) {
            throw new WizardWfIsRequiredException($this, $targetProjectDirectory);
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    public function isBuilt($targetProjectDirectory)
    {
        return parent::isBuilt($targetProjectDirectory)
            && $this->composerInstalledVersionParser->get($targetProjectDirectory, 'deployer/deployer');
    }

    protected function getBuiltCheckFile()
    {
        return 'deploy.php';
    }

    /**
     * @param BuildWizardEvent $event
     */
    public function build(BuildWizardEvent $event)
    {
        $this->runCmdInContainer('composer require --dev deployer/deployer', $event->getWorkingDirectory());
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        $targetProjectDirectory = $event->getWorkingDirectory();

        $variables = $this->ezEnvironmentParser->getSymfonyEnvironmentVariables($targetProjectDirectory);
        $variables['is_ez'] = $this->ezEnvironmentParser->isEzProject($targetProjectDirectory);
        $variables['project_directory'] = basename($this->envParser->get('ORIGINAL_PWD', $targetProjectDirectory));

        try {
            $gitRemoteOrigin = trim($this->commander->run('git config --get remote.origin.url', $targetProjectDirectory));
        } catch (CommanderRunException $e) {
            $gitRemoteOrigin = false;
        }
        if (!$gitRemoteOrigin) {
            $this->ioManager->getIo()->title('Missing <info>remote.origin.url</info>');
            $question = new Question('You have to set the git remote origin url: ', '--you-have-to-set-it--');
            $gitRemoteOrigin = $this->ask($question);
        }

        $variables['remote_url'] = trim($gitRemoteOrigin);
        $variables['project_name'] = basename($this->envParser->get('ORIGINAL_PWD', $targetProjectDirectory));

        return $variables;
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
        parent::eventBeforeDumpTargetExists($event);

        switch ($event->getSkeletonFile()->getBaseFileInfo()->getFilename()) {
            case '.gitignore':
                $event->getSkeletonFile()->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
        }
    }
}
