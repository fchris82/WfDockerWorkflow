<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 15:44.
 */

namespace App\Wizard\Base;

use App\Wizard\BaseSkeletonWizard;
use App\Wizard\Helper\ComposerInstaller;
use App\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DeployerSkeleton extends BaseSkeletonWizard implements PublicWizardInterface
{
    public function getName()
    {
        return 'Deployer (not compatible with SF4 yet!)';
    }

    public function getInfo()
    {
        return 'Add Deployer (not compatible with SF4 yet!)';
    }

    protected function setVariables($targetProjectDirectory)
    {
        try {
            $kaliopVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'kaliop/ezmigrationbundle');
        } catch (\InvalidArgumentException $e) {
            $kaliopVersion = false;
        }
        $variables['is_ez'] = $kaliopVersion ? true : false;
        $variables['project_directory'] = basename($this->getEnv('ORIGINAL_PWD'));

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
                $this->filesystem->dumpFile($targetPath, $fileContent);

                if ($permission) {
                    $this->filesystem->chmod($targetPath, $permission);
                }
        }
    }

    protected function getBuiltCheckFile()
    {
        return 'deployer.php';
    }

    protected function getSkeletonTemplateDirectory()
    {
        return 'Deployer';
    }

    public function getRequireComposerPackages()
    {
        return [ComposerInstaller::COMPOSER_DEV => ['deployer/deployer']];
    }
}
