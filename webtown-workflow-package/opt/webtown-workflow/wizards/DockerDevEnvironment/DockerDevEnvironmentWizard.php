<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:46
 */

namespace Wizards\DockerDevEnvironment;
use App\Wizard\WizardInterface;
use Symfony\Component\Console\Question\Question;
use Wizards\Docker\BaseDocker;

/**
 * Class DevEnvironment.
 *
 * Add "hidden" and simple WF einvironment to a project to work.
 *
 * <code>
 *  DockerProjectSlim
 *  ├── .wf.yml     <-- gitignore configuration file
 *  │
 *  └── [...]       <-- Projekt többi fájlja
 * </code>
 */
class DockerDevEnvironmentWizard extends BaseDocker implements WizardInterface
{
    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.2</info>]', '7.2');
        $variables['php_version'] = $this->ask($phpVersionQuestion);
        $variables['project_name'] = basename($targetProjectDirectory);

        return $variables;
    }

    public function getDefaultName()
    {
        return 'Docker Environment - Git outside';
    }

    public function getInfo()
    {
        return 'Create a docker environment for a project, hidden from git. You have to register the <info>/.wf.yml</info>' .
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

    protected function build($targetProjectDirectory)
    {
    }
}
