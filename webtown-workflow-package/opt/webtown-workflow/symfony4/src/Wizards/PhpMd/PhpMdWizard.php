<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 11:30
 */

namespace App\Wizards\PhpMd;

use App\Environment\Commander;
use App\Environment\EzEnvironmentParser;
use App\Environment\IoManager;
use App\Environment\WfEnvironmentParser;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Wizards\BaseSkeletonWizard;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

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
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->ezEnvironmentParser = $ezEnvironmentParser;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
    }

    public function getDefaultName()
    {
        return 'PhpMd install';
    }

    public function getInfo()
    {
        return 'Add PhpMd to the project.';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    public function getBuiltCheckFile()
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

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        return $this->ezEnvironmentParser->getSymfonyEnvironmentVariables($event->getWorkingDirectory());
    }

    /**
     * @param BuildWizardEvent $event
     */
    public function build(BuildWizardEvent $event)
    {
        $this->runCmdInContainer('composer require --dev phpmd/phpmd', $event->getWorkingDirectory());
    }
}
