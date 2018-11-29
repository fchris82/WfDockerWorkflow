<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.02.19.
 * Time: 11:52
 */

namespace App\Wizards\Symfony;

use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use Symfony\Component\Console\Question\Question;
use App\Wizards\BaseSkeletonWizard;

class SymfonyBuildWizard extends BaseSkeletonWizard
{
    protected $askDirectory = true;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $workingDirectory;

    public function getDefaultName()
    {
        return 'Symfony builder';
    }

    public function getInfo()
    {
        return 'Create a symfony project';
    }

    public function getDefaultGroup()
    {
        return 'Builder';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return $this->wfIsInitialized($targetProjectDirectory) || file_exists($targetProjectDirectory . '/.git');
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent)
    {
        parent::eventAfterBuildFile($postBuildSkeletonFileEvent);

        $postBuildSkeletonFileEvent->getSkeletonFile()->move($this->workingDirectory);
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        $directoryQuestion = new Question('Add meg a könyvtárat, ahová szeretnéd telepíteni: [<info>.</info>] ', '.');
        $versionQuestion = new Question('Add meg verziót [Üresen hagyva a legutóbbi stabil verziót szedi le, egyébként: <info>x.x</info>] ');

        $directory = $this->askDirectory
            ? $this->ask($directoryQuestion)
            : '.';
        $this->workingDirectory = $event->getWorkingDirectory() . \DIRECTORY_SEPARATOR . $directory;
        $event->setWorkingDirectory($this->workingDirectory);

        $version = $this->ask($versionQuestion);
        $sfVersion = $version ?
            $version[1] :
            4;
        $this->config = [
            'version'                  => $version,
            'sf_version'               => $sfVersion,
        ];

        return $this->config;
    }

    protected function build(BuildWizardEvent $event)
    {
        // Alapértelmezett adatok
        $package = 'symfony/website-skeleton';
        // Itt jegyezzük be, ha vmi config-ot módosítani kell. Elérhető configok: `composer config --list`
        $composerConfigChanges = [];
        $version = $this->config['version'];
        if ($version && version_compare($version, '4', '<')) {
            $package = 'symfony/framework-standard-edition';
            // SF3-ban 5.4 van megadva, ami nekünk nagyon nem jó, régi
            $composerConfigChanges = [
                'platform.php' => '7.1',
            ];
        }

        $workDir = $event->getWorkingDirectory();
        $tmpDir = $workDir . '/_tmp';
        $this->run('mkdir -p ' . $tmpDir);

        $this->cd($workDir);
        $this->runCmdInContainer(sprintf(
            'composer create-project %s %s %s',
            $package,
            $tmpDir,
            $version ? '"' . $version . '"' : ''
        ));

        // Composer config upgrade, eg: platform.php --> 7.1
        if (\count($composerConfigChanges) > 0) {
            foreach ($composerConfigChanges as $key => $value) {
                $this->runCmdInContainer(sprintf('composer config %s %s', $key, $value), $tmpDir);
            }
            $this->runCmdInContainer('composer update', $tmpDir);
        }

        $this->run('rm -rf .[^.] .??*');
        $this->run('mv _tmp/* ./');
        $this->run('mv _tmp/.[!.]* ./');
        $this->run('rm -r _tmp');
        $this->run('git init && git add . && git commit -m "Init"');
    }
}
