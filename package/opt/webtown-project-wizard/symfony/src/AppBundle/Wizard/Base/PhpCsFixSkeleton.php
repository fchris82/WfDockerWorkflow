<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 15:44.
 */

namespace AppBundle\Wizard\Base;

use AppBundle\Wizard\BaseSkeletonWizard;
use AppBundle\Wizard\Helper\ComposerInstaller;
use AppBundle\Wizard\PublicWizardInterface;

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

    protected function doWriteFile($targetPath, $fileContent, $relativePathName)
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

    public function build($targetProjectDirectory)
    {
        return parent::build($targetProjectDirectory);
    }

    public function getComposerPackages()
    {
        return [ComposerInstaller::COMPOSER_DEV => ['friendsofphp/php-cs-fixer']];
    }
}
