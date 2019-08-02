<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 11:30
 */

namespace App\Wizards\PhpMd;

use Webtown\WorkflowBundle\Environment\Commander;
use Webtown\WorkflowBundle\Environment\EzEnvironmentParser;
use Webtown\WorkflowBundle\Environment\IoManager;
use Webtown\WorkflowBundle\Environment\WfEnvironmentParser;
use Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use Webtown\WorkflowBundle\Exception\WizardWfIsRequiredException;
use Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Environment;

class PhpMdWizard extends BaseSkeletonWizard
{
    /**
     * @var EzEnvironmentParser
     */
    protected $ezEnvironmentParser;

    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    public function __construct(
        EzEnvironmentParser $ezEnvironmentParser,
        WfEnvironmentParser $wfEnvironmentParser,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->ezEnvironmentParser = $ezEnvironmentParser;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
    }

    public function getDefaultName(): string
    {
        return 'PhpMd install';
    }

    public function getInfo(): string
    {
        return 'Add PhpMd to the project.';
    }

    public function getDefaultGroup(): string
    {
        return 'Composer';
    }

    public function getBuiltCheckFile(): string
    {
        return 'phpmd.xml';
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

    protected function readSkeletonVars(BuildWizardEvent $event): array
    {
        return $this->ezEnvironmentParser->getSymfonyEnvironmentVariables($event->getWorkingDirectory());
    }

    /**
     * @param BuildWizardEvent $event
     */
    public function build(BuildWizardEvent $event): void
    {
        $this->runCmdInContainer('composer require --dev phpmd/phpmd', $event->getWorkingDirectory());
    }
}
