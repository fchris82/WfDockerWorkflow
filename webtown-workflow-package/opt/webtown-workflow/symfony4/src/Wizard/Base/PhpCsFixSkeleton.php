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

class PhpCsFixSkeleton extends BaseSkeletonWizard implements PublicWizardInterface
{
    public function getName()
    {
        return 'PHP-CS fixer';
    }

    public function getInfo()
    {
        return 'Add PHP-CS fixer';
    }

    protected function setVariables($targetProjectDirectory)
    {
        return [];
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
                // @todo (Chris) Ezt átírni intelligensre, hogy rákérdez, ha felülírna egy másik fájlt.
                $this->filesystem->dumpFile($targetPath, $fileContent);

                if ($permission) {
                    $this->filesystem->chmod($targetPath, $permission);
                }
        }
    }

    protected function getBuiltCheckFile()
    {
        return '.php_cs.dist';
    }

    protected function getSkeletonTemplateDirectory()
    {
        return 'PhpCsFixer';
    }

    public function getRequireComposerPackages()
    {
        return [ComposerInstaller::COMPOSER_DEV => ['friendsofphp/php-cs-fixer']];
    }
}
