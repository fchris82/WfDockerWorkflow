<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:35
 */

namespace App\Wizards\PhpCsFixer;

use App\Environment\Commander;
use App\Environment\IoManager;
use App\Environment\SymfonyEnvironmentParser;
use App\Environment\WfEnvironmentParser;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Skeleton\FileType\SkeletonFile;
use App\Wizards\BaseSkeletonWizard;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

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
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->wfEnvironmentParser = $wfEnvironmentParser;
        $this->symfonyEnvironmentParser = $symfonyEnvironmentParser;
    }

    public function getDefaultName()
    {
        return 'PhpCsFixer install';
    }

    public function getInfo()
    {
        return 'Add PhpCsFixer to the project.';
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

    protected function getBuiltCheckFile()
    {
        return '.php_cs.dist';
    }

    /**
     * @param BuildWizardEvent $event
     */
    public function build(BuildWizardEvent $event)
    {
        $this->runCmdInContainer('composer require --dev friendsofphp/php-cs-fixer', $event->getWorkingDirectory());
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
