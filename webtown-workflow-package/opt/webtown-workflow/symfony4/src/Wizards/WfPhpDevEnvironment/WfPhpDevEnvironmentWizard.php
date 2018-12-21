<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:46
 */

namespace App\Wizards\WfPhpDevEnvironment;

use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Wizards\WfDevEnvironment\WfDevEnvironmentWizard;
use Symfony\Component\Console\Question\Question;

/**
 * Class DevEnvironment.
 *
 * Add "hidden" and simple WF einvironment to a project to work.
 *
 * <code>
 *  DockerProject
 *      ├── [...]
 *      ├── .wf.yml     <-- gitignored configuration file
 *      └── [...]
 * </code>
 */
class WfPhpDevEnvironmentWizard extends WfDevEnvironmentWizard
{
    public function getDefaultName()
    {
        return 'WF PHP Environment - Git ignored/outside';
    }

    public function getInfo()
    {
        return 'Create a WF environment for a project, hidden from git. You have to register the <info>/.wf.yml</info>' .
            ' in your <comment>global .gitignore</comment> file! You must use this with third party bundles or other components.';
    }

    public function getDefaultGroup()
    {
        return 'WF';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return $this->wfEnvironmentParser->wfIsInitialized($targetProjectDirectory);
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        $variables = parent::readSkeletonVars($event);

        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.2</info>]', '7.2');
        $variables['php_version'] = $this->ask($phpVersionQuestion);

        return $variables;
    }
}
