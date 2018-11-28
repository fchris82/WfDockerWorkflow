<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:46
 */

namespace Wizards\WfDevEnvironment;

use App\Event\Wizard\BuildWizardEvent;
use Wizards\BaseSkeletonWizard;

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
class WfDevEnvironmentWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'WF Environment - Git ignored/outside';
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
        return $this->wfIsInitialized($targetProjectDirectory);
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        return [
            'project_name' => basename($event->getWorkingDirectory()),
        ];
    }

    protected function build(BuildWizardEvent $event)
    {
        $this->output->writeln('<comment>We created a simple and "empty" <info>.wf.yml</info> file, you have to edit it!</comment>');
        $this->output->writeln('');
        $this->output->writeln(file_get_contents($event->getWorkingDirectory() . \DIRECTORY_SEPARATOR . '.wf.yml'));
        $this->output->writeln('');
        $this->output->writeln('<question>List available recipes:</question>');
        $this->output->writeln('<info>wf --config-dump --only-recipes</info>');
        $this->output->writeln('<question>Add full recipe config with defaults:</question>');
        $this->output->writeln('<info>wf --config-dump --recipe=<comment>[recipe_name]</comment> --no-ansi >> .wf.yml</info>');
    }
}
