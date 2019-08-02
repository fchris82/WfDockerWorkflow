<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:35
 */

namespace App\Wizards\PhpCsFixer;

use Webtown\WorkflowBundle\Environment\Commander;
use Webtown\WorkflowBundle\Environment\IoManager;
use Webtown\WorkflowBundle\Environment\SymfonyEnvironmentParser;
use Webtown\WorkflowBundle\Environment\WfEnvironmentParser;
use Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use Webtown\WorkflowBundle\Exception\WizardWfIsRequiredException;
use Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class PhpCsFixerWizard extends BaseSkeletonWizard
{
    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    /**
     * @var SymfonyEnvironmentParser
     */
    protected $symfonyEnvironmentParser;

    public function __construct(
        WfEnvironmentParser $wfEnvironmentParser,
        SymfonyEnvironmentParser $symfonyEnvironmentParser,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->wfEnvironmentParser = $wfEnvironmentParser;
        $this->symfonyEnvironmentParser = $symfonyEnvironmentParser;
    }

    public function getDefaultName(): string
    {
        return 'PhpCsFixer install';
    }

    public function getInfo(): string
    {
        return 'Add PhpCsFixer to the project.';
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

    protected function getBuiltCheckFile(): string
    {
        return '.php_cs.dist';
    }

    /**
     * @param BuildWizardEvent $event
     */
    public function build(BuildWizardEvent $event): void
    {
        $this->runCmdInContainer('composer require --dev friendsofphp/php-cs-fixer', $event->getWorkingDirectory());
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
