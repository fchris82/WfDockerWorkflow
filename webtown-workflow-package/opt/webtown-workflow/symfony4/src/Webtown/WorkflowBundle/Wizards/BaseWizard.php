<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace App\Webtown\WorkflowBundle\Wizards;

use App\Webtown\WorkflowBundle\Environment\Commander;
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Exception\WizardHasAlreadyBuiltException;
use App\Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use App\Webtown\WorkflowBundle\Wizard\WizardInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class BaseSkeleton.
 */
abstract class BaseWizard implements WizardInterface
{
    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * @var Commander
     */
    protected $commander;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    public function __construct(IoManager $ioManager, Commander $commander, EventDispatcherInterface $eventDispatcher)
    {
        $this->ioManager = $ioManager;
        $this->commander = $commander;
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

    public function ask(Question $question)
    {
        return $this->ioManager->ask($question);
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
        try {
            $wizard->checkRequires($workingDirectory);
            if (!$wizard->isBuilt($workingDirectory)) {
                try {
                    $wizard->runBuild($workingDirectory);
                } catch (WizardHasAlreadyBuiltException $e) {
                    $this->ioManager->getIo()->note($e->getMessage());
                }
            }
        } catch (WizardSomethingIsRequiredException $e) {
            $this->ioManager->writeln($e->getMessage());
        }
    }

    public function runCmdInContainer($cmd, $workdir = null)
    {
        return $this->commander->runCmdInContainer(
            $cmd,
            $this->getDockerImage(),
            $this->getDockerCmdExtraParameters($workdir),
            $workdir
        );
    }

    protected function getDockerCmdExtraParameters($targetProjectDirectory)
    {
        return '';
    }

    /**
     * We are using this when call a self::runCmdInContainer() function.
     *
     * @return string
     *
     * @see BaseWizard::runCmdInContainer()
     */
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
