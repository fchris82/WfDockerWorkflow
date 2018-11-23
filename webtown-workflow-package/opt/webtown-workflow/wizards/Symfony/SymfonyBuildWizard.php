<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.02.19.
 * Time: 11:52
 */

namespace Wizards\Symfony;

use Wizards\BaseSkeletonWizard;
use Symfony\Component\Console\Question\Question;
use Wizards\BaseWizard;

class SymfonyBuildWizard extends BaseSkeletonWizard
{
    protected $askDirectory = true;

    /**
     * @var array
     */
    protected $config = [];

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

    /**
     * @param string $targetProjectDirectory
     *
     * @return mixed|string
     *
     * @throws \App\Exception\WizardHasAlreadyBuiltException
     * @throws \Exception
     */
    public function initBuild($targetProjectDirectory)
    {
        BaseWizard::initBuild($targetProjectDirectory);

        $templateVariables = $this->setVariables($targetProjectDirectory);
        $targetProjectDirectory = $this->config['target_project_directory'];
        $this->printHeader($templateVariables);
        $this->doBuildFiles($targetProjectDirectory, $templateVariables);

        return $targetProjectDirectory;
    }

    protected function setVariables($targetProjectDirectory)
    {
        $directoryQuestion = new Question('Add meg a könyvtárat, ahová szeretnéd telepíteni: [<info>.</info>] ', '.');
        $versionQuestion = new Question('Add meg verziót [Üresen hagyva a legutóbbi stabil verziót szedi le, egyébként: <info>x.x</info>] ');

        $directory = $this->askDirectory
            ? $this->ask($directoryQuestion)
            : '.';

        $version = $this->ask($versionQuestion);
        $sfVersion = $version ?
            $version[1] :
            4;
        $this->config = [
            'target_project_directory' => $targetProjectDirectory . DIRECTORY_SEPARATOR . $directory,
            'version'                  => $version,
            'sf_version'               => $sfVersion,
        ];

        return $this->config;
    }

    /**
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory)
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

        $workDir = $this->config['target_project_directory'];
        $tmpDir = $workDir . '/_tmp';
        $this->cd($targetProjectDirectory);
        $this->run('mkdir -p ' . $tmpDir);

        $this->cd($workDir);
        $this->runCmdInContainer(sprintf(
            'composer create-project %s %s %s',
            $package,
            $tmpDir,
            $version ? '"' . $version . '"' : ''
        ));

        // Composer config upgrade, eg: platform.php --> 7.1
        if (count($composerConfigChanges) > 0) {
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

        return $targetProjectDirectory;
    }
}
