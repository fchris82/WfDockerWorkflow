<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:55
 */

namespace Wizards\Deployer;


use App\Exception\WizardWfIsRequiredException;
use Wizards\BaseSkeletonWizard;

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
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory)
    {
        $this->run(sprintf('cd %s && wf composer require --dev deployer/deployer', $targetProjectDirectory));
    }

    protected function setVariables($targetProjectDirectory)
    {
        try {
            $ezVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'ezsystems/ezpublish-kernel');
            $kaliopVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'kaliop/ezmigrationbundle');
            $ezYmlExists = file_exists($targetProjectDirectory . '/.ez.yml');
        } catch (\InvalidArgumentException $e) {
            $kaliopVersion = false;
        }
        $variables['is_ez'] = $ezVersion || $kaliopVersion || $ezYmlExists ? true : false;
        $variables['project_directory'] = basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory));

        $variables['remote_url'] = trim(implode("\n", $this->run(sprintf('cd %s && git config --get remote.origin.url', $targetProjectDirectory))));
        $variables['project_name'] = basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory));

        return $variables;
    }

    /**
     * Eltérő fájloknál eltérő műveletet kell alkalmazni. Vhol simán létre kell hozni a fájlt, vhol viszont append-elni
     * kell a már létezőt, párnál pedig YML-lel kell összefésülni az adatokat.
     * <code>
     *  switch ($targetPath) {
     *      case '/this/is/an/existing/file':
     *          $this->filesystem->appendToFile($targetPath, $fileContent);
     *          break;
     *      default:
     *          $this->filesystem->dumpFile($targetPath, $fileContent);
     *  }
     * </code>.
     *
     * @param string $targetPath
     * @param string $fileContent
     * @param string $relativePathName
     * @param int    $permission
     */
    protected function doWriteFile($targetPath, $fileContent, $relativePathName, $permission = null)
    {
        $append = [
            '.gitignore',
        ];
        switch (true) {
            case in_array($relativePathName, $append):
                $this->filesystem->appendToFile($targetPath, $fileContent);
                break;
            default:
                parent::doWriteFile($targetPath, $fileContent, $relativePathName, $permission);
        }
    }
}
