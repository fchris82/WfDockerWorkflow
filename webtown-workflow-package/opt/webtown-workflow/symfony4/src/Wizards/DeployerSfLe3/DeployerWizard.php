<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:55
 */

namespace App\Wizards\DeployerSfLe3;

use App\Webtown\WorkflowBundle\Environment\Commander;
use App\Webtown\WorkflowBundle\Environment\EnvParser;
use App\Webtown\WorkflowBundle\Environment\EzEnvironmentParser;
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Environment\MicroParser\ComposerInstalledVersionParser;
use App\Webtown\WorkflowBundle\Environment\WfEnvironmentParser;
use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use App\Webtown\WorkflowBundle\Exception\WizardWfIsRequiredException;
use App\Wizards\Deployer\DeployerWizard as BaseDeployerWizard;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class DeployerWizard extends BaseDeployerWizard
{
    /**
     * @var EzEnvironmentParser
     */
    protected $ezEnvironmentParser;

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
        parent::__construct(
            $composerInstalledVersionParser,
            $wfEnvironmentParser,
            $envParser,
            $ioManager,
            $commander,
            $eventDispatcher,
            $twig,
            $filesystem
        );
        $this->ezEnvironmentParser = $ezEnvironmentParser;
    }

    public function getDefaultName()
    {
        return 'Deployer (SF <= 3)';
    }

    public function getInfo()
    {
        return 'Add Deployer for a Symfony project (SF <= 3)';
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
        parent::checkRequires($targetProjectDirectory);

        $this->checkSfVersion($targetProjectDirectory, 4, '<');

        return true;
    }

    /**
     * @param $targetProjectDirectory
     * @param $version
     * @param $operator
     *
     * @throws WizardSomethingIsRequiredException
     */
    protected function checkSfVersion($targetProjectDirectory, $version, $operator)
    {
        $sfVersion = $this->ezEnvironmentParser->getSymfonyVersion($targetProjectDirectory);
        if ($sfVersion && !version_compare($sfVersion, $version, $operator)) {
            throw new WizardSomethingIsRequiredException(sprintf(
                'The required Symfony version is: %s%s. Your current SF version is: %s',
                $operator,
                $version,
                $sfVersion ?: 'not installed/unknown'
            ));
        }
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        $targetProjectDirectory = $event->getWorkingDirectory();

        $variables = parent::readSkeletonVars($event);
        $sfVariables = $this->ezEnvironmentParser->getSymfonyEnvironmentVariables($targetProjectDirectory);
        $variables = array_merge($variables, $sfVariables);

        return $variables;
    }

    public static function getSkeletonParents()
    {
        return [BaseDeployerWizard::class];
    }
}
