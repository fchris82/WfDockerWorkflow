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

class PhpMdSkeleton extends BaseSkeletonWizard implements PublicWizardInterface
{
    public function getName()
    {
        return 'PHPMD';
    }

    public function getInfo()
    {
        return 'Add phpmd.xml';
    }

    protected function setVariables($targetProjectDirectory)
    {
        return [];
    }

    protected function getBuiltCheckFile()
    {
        return 'phpmd.xml';
    }

    protected function getSkeletonTemplateDirectory()
    {
        return 'PhpMd';
    }

    public function build($targetProjectDirectory)
    {
        return parent::build($targetProjectDirectory);
    }

    /**
     * 'dev' => [... dev packages ...]
     * 'nodev' => [... nodev packages ...].
     *
     * Eg:
     * <code>
     *  return ['dev' => ["friendsofphp/php-cs-fixer:~2.3.3"]];
     * </code>
     *
     * @return array
     */
    public function getRequireComposerPackages()
    {
        return [ComposerInstaller::COMPOSER_DEV => ['phpmd/phpmd']];
    }
}
