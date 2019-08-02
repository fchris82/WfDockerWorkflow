<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:55
 */

namespace App\Wizards\Deployer;

use Webtown\WorkflowBundle\Environment\Commander;
use Webtown\WorkflowBundle\Environment\EnvParser;
use Webtown\WorkflowBundle\Environment\IoManager;
use Webtown\WorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use Webtown\WorkflowBundle\Environment\WfEnvironmentParser;
use Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Webtown\WorkflowBundle\Exception\CommanderRunException;
use Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use Webtown\WorkflowBundle\Exception\WizardWfIsRequiredException;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

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
     * @var EnvParser
     */
    protected $envParser;

    public function __construct(
        ComposerInstalledVersionParser $composerInstalledVersionParser,
        WfEnvironmentParser $wfEnvironmentParser,
        EnvParser $envParser,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->composerInstalledVersionParser = $composerInstalledVersionParser;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
        $this->envParser = $envParser;
    }

    public function getDefaultName(): string
    {
        return 'Deployer (base)';
    }

    public function getInfo(): string
    {
        return 'Add Deployer';
    }

    public function getDefaultGroup(): string
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
    public function checkRequires(string $targetProjectDirectory): bool
    {
        if (!file_exists($targetProjectDirectory . '/composer.json')) {
            throw new WizardSomethingIsRequiredException(sprintf('Initialized composer is required for this!'));
        }
        if (!$this->wfEnvironmentParser->wfIsInitialized($targetProjectDirectory)) {
            throw new WizardWfIsRequiredException($this, $targetProjectDirectory);
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    public function isBuilt($targetProjectDirectory): bool
    {
        return parent::isBuilt($targetProjectDirectory)
            && $this->composerInstalledVersionParser->get($targetProjectDirectory, 'deployer/deployer');
    }

    protected function getBuiltCheckFile(): string
    {
        return 'deploy.php';
    }

    /**
     * @param BuildWizardEvent $event
     */
    public function build(BuildWizardEvent $event): void
    {
        $this->runCmdInContainer('composer require --dev deployer/deployer', $event->getWorkingDirectory());
    }

    protected function readSkeletonVars(BuildWizardEvent $event): array
    {
        $targetProjectDirectory = $event->getWorkingDirectory();

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

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event): void
    {
        parent::eventBeforeDumpTargetExists($event);

        switch ($event->getSkeletonFile()->getBaseFileInfo()->getFilename()) {
            case '.gitignore':
                $event->getSkeletonFile()->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
        }
    }
}
