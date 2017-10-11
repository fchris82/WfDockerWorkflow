<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 15:44.
 */

namespace AppBundle\Wizard\Base;

use AppBundle\Wizard\BaseSkeletonWizard;
use AppBundle\Wizard\PublicWizardInterface;

class GitlabCISkeleton extends BaseSkeletonWizard implements PublicWizardInterface
{
    public function getName()
    {
        return 'GitlabCI';
    }

    public function getInfo()
    {
        return 'Add .gitlab-ci.yml';
    }

    protected function setVariables($targetProjectDirectory)
    {
        return ['project_name' => sprintf('project_%s', date('YmdHis'))];
    }

    protected function doWriteFile($targetPath, $fileContent, $relativePathName)
    {
        // @todo (Chris) Ezt átírni intelligensre, hogy rákérdez, ha felülírna egy másik fájlt.
        $this->filesystem->dumpFile($targetPath, $fileContent);
    }

    protected function getBuiltCheckFile()
    {
        return '.gitlab-ci.yml';
    }

    protected function getSkeletonTemplateDirectory()
    {
        return 'GitlabCIProject';
    }

    public function build($targetProjectDirectory)
    {
        return parent::build($targetProjectDirectory);
    }

    public function getComposerPackages()
    {
        return [];
    }
}
