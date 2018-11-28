<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:51
 */

namespace Wizards\WfSymfonyEnvironment;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Wizards\BaseSkeletonWizard;

/**
 * Class DockerProject.
 *
 * Lapos könyvtárstruktúrát húz rá a projektre.
 *
 * <code>
 *  DockerProject
 *  ├── [...]
 *  │
 *  └── .wf.yml.dist
 * </code>
 */
class WfEnvironmentWizardProjectWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'WF Symfony Environment';
    }

    public function getInfo()
    {
        return 'Create a Symfony WF environment for the existing project.';
    }

    public function getDefaultGroup()
    {
        return 'WF';
    }

    protected function build(BuildWizardEvent $event)
    {
        // TODO: Implement build() method.
    }

    /**
     * Az itt visszaadott fájllal ellenőrizzük, hogy az adott dekorátor lefutott-e már.
     * <code>
     *  protected function getBuiltCheckFile() {
     *      return '.docker';
     *  }
     * </code>.
     *
     * @return string
     */
    protected function getBuiltCheckFile()
    {
        return '.wf.yml.dist';
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        $targetProjectDirectory = $event->getWorkingDirectory();
        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.2</info>]', '7.2');
        $variables['php_version'] = $this->ask($phpVersionQuestion);

        // Megpróbáljuk kiolvasni a használt SF verziót, már ha létezik
        $symfonyVersion = $this->getSymfonyVersion($targetProjectDirectory);

        if (!$symfonyVersion) {
            $symfonyVersionQuestion = new ChoiceQuestion(
                'Which symfony version do you want to use? [<info>4.*</info>]',
                ['4.*', '3.* (eZ project + LTE)', '2.* [deprecated]'],
                0
            );
            $symfonyVersion = $this->ask($symfonyVersionQuestion);
        }
        switch (substr($symfonyVersion, 0, 2)) {
            case '4.':
                $variables['sf_version']     = 4;
                $variables['sf_console_cmd'] = 'bin/console';
                $variables['sf_bin_dir']     = $this->readSymfonyBinDir($targetProjectDirectory, 'vendor/bin');
                $variables['shared_dirs']    = 'var';
                $variables['web_directory']  = 'public';
                $variables['index_file']     = 'index.php';
                break;
            case '3.':
                $variables['sf_version']     = 3;
                $variables['sf_console_cmd'] = 'bin/console';
                $variables['sf_bin_dir']     = $this->readSymfonyBinDir($targetProjectDirectory, 'vendor/bin');
                $variables['shared_dirs']    = 'var';
                $variables['web_directory']  = 'web';
                $variables['index_file']     = 'app.php';
                break;
            case '2.':
                $variables['sf_version']     = 2;
                $variables['sf_console_cmd'] = 'app/console';
                $variables['sf_bin_dir']     = $this->readSymfonyBinDir($targetProjectDirectory, 'bin');
                $variables['shared_dirs']    = 'app/cache app/logs';
                $variables['web_directory']  = 'web';
                $variables['index_file']     = 'app.php';
                break;
            default:
                throw new \InvalidArgumentException('Invalid selection! Missiong settings!');
        }

        $variables['project_name'] = basename($this->getEnv('ORIGINAL_PWD', $event->getWorkingDirectory()));
        $variables['current_wf_version'] = $this->input->getOption('wf-version');

        return $variables;
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
        parent::eventBeforeDumpTargetExists($event);

        switch (strtolower($event->getSkeletonFile()->getBaseFileInfo()->getFilename())) {
            case '.gitignore':
            case 'readme.md':
                $event->getSkeletonFile()->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
        }
    }
}
