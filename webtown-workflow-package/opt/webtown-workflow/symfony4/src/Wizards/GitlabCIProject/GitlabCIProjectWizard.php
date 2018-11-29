<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:45
 */

namespace App\Wizards\GitlabCIProject;

use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Wizards\BaseSkeletonWizard;

class GitlabCIProjectWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'GitlabCI';
    }

    public function getInfo()
    {
        return 'Initialize projet to Gitlab CI';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        $targetProjectDirectory = $event->getWorkingDirectory();
        $wfConfiguration = $this->getWorkflowConfiguration($targetProjectDirectory);
        $symfonyRecipeName = null;
        foreach ($wfConfiguration['recipes'] as $recipeName => $recipeConfig) {
            if (0 === strpos($recipeName, 'symfony')) {
                $symfonyRecipeName = $recipeName;
                break;
            }
        }
        $installedSfVersion = $this->getSymfonyVersion($targetProjectDirectory);
        if (!$installedSfVersion) {
            throw new \Exception('We don\'t find any symfony package!');
        }
        $sfConsoleCmd = version_compare($installedSfVersion, '3.0', '>=')
            ? 'bin/console'
            : 'app/console';
        // If the bin path is overridden in composer.json file, we use it.
        $sfBinDir = $this->readSymfonyBinDir($targetProjectDirectory);
        // If it doesn't exist in composer.json, we set it from SF version.
        if (!$sfBinDir) {
            $sfBinDir = version_compare($installedSfVersion, '3.0', '>=')
                ? 'vendor/bin'
                : 'bin';
        }

        return [
            'project_name' => basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory)),
            'sf_recipe_name' => $symfonyRecipeName,
            'sf_console_cmd' => $sfConsoleCmd,
            'sf_bin_dir' => $sfBinDir,
        ];
    }

    protected function getBuiltCheckFile()
    {
        return '.gitlab-ci.yml';
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
        if (!$this->wfIsInitialized($targetProjectDirectory)) {
            throw new WizardWfIsRequiredException($this, $targetProjectDirectory);
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @return string
     */
    public function build(BuildWizardEvent $event)
    {
        $workingDirectory = $event->getWorkingDirectory();
        // Ha létezik parameters.yml, akkor annak is létrehozunk egy gitlab verziót
        if ($this->fileSystem->exists($workingDirectory . '/app/config/parameters.yml.dist')) {
            $this->fileSystem->copy(
                $workingDirectory . '/app/config/parameters.yml.dist',
                $workingDirectory . '/app/config/parameters.gitlab-ci.yml'
            );
            $this->output->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                'app/config',
                'parameters.gitlab-ci.yml'
            ));
        }
    }
}
