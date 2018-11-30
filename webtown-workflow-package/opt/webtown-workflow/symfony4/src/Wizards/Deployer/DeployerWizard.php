<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:55
 */

namespace App\Wizards\Deployer;

use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Wizards\BaseSkeletonWizard;

class DeployerWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'Deployer';
    }

    public function getInfo()
    {
        return 'Add Deployer';
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
        if (!$this->wfIsInitialized($targetProjectDirectory)) {
            throw new WizardWfIsRequiredException($this, $targetProjectDirectory);
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    protected function getBuiltCheckFile()
    {
        return 'deploy.php';
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @return string
     */
    public function build(BuildWizardEvent $event)
    {
        $this->runCmdInContainer('composer require --dev deployer/deployer', $event->getWorkingDirectory());
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        try {
            $targetProjectDirectory = $event->getWorkingDirectory();
            $ezVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'ezsystems/ezpublish-kernel');
            $kaliopVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'kaliop/ezmigrationbundle');
            $ezYmlExists = file_exists($targetProjectDirectory . '/.ez.yml');
        } catch (\InvalidArgumentException $e) {
            $kaliopVersion = false;
        }
        $variables['is_ez'] = $ezVersion || $kaliopVersion || $ezYmlExists ? true : false;
        $variables['project_directory'] = basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory));

        $gitRemoteOrigin = $this->run('git config --get remote.origin.url', $targetProjectDirectory, function ($return, $output) {
            if (0 === $return && trim($output)) {
                return $output;
            }

            $io = new SymfonyStyle($this->input, $this->output);
            $io->title('Missing <info>remote.origin.url</info>');
            $question = new Question('You have to set the git remote origin url: ', '--you-have-to-set-it--');
            return $this->ask($question);
        });
        $variables['remote_url'] = trim($gitRemoteOrigin);
        $variables['project_name'] = basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory));

        return $variables;
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
