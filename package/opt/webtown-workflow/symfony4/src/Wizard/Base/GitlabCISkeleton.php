<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 15:44.
 */

namespace App\Wizard\Base;

use App\Wizard\BaseSkeletonWizard;
use App\Wizard\PublicWizardInterface;

class GitlabCISkeleton extends BaseSkeletonWizard implements PublicWizardInterface
{
    public function getName()
    {
        return 'GitlabCI';
    }

    public function getInfo()
    {
        return 'Initialize projet to Gitlab CI';
    }

    protected function setVariables($targetProjectDirectory)
    {
        $installedSfVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'symfony/symfony');
        $sfConsoleCmd = version_compare($installedSfVersion, '3.0', '>=')
            ? 'bin/console'
            : 'app/console';
        $sfBinDir = version_compare($installedSfVersion, '3.0', '>=')
            ? 'vendor/bin'
            : 'bin';

        return [
            'project_name' => sprintf('project_%s', date('YmdHis')),
            'sf_console_cmd' => $sfConsoleCmd,
            'sf_bin_dir' => $sfBinDir,
        ];
    }

    protected function getBuiltCheckFile()
    {
        return '.gitlab-ci.yml';
    }

    protected function getSkeletonTemplateDirectory()
    {
        return 'GitlabCIProject';
    }

    protected function getTemplatesFinder($targetProjectDirectory)
    {
        $finder = parent::getTemplatesFinder($targetProjectDirectory);

        $onlyIfExists = [
            '.docker',
        ];
        foreach ($onlyIfExists as $directoryOrFileRelPath) {
            $checkPath = $targetProjectDirectory . '/' . $directoryOrFileRelPath;
            if (!$this->filesystem->exists($checkPath)) {
                $finder->exclude($directoryOrFileRelPath);
            }
        }

        return $finder;
    }

    /**
     * @param $targetProjectDirectory
     * @return string|void
     * @throws \App\Exception\ProjectHasDecoratedException
     */
    public function build($targetProjectDirectory)
    {
        $this->currentProjectDirectory = $targetProjectDirectory;
        $targetProjectDirectory = parent::build($targetProjectDirectory);

        // Ha létezik parameters.yml, akkor annak is létrehozunk egy gitlab verziót
        if ($this->filesystem->exists($targetProjectDirectory . '/app/config/parameters.yml.dist')) {
            $this->filesystem->copy(
                $targetProjectDirectory . '/app/config/parameters.yml.dist',
                $targetProjectDirectory . '/app/config/parameters.gitlab-ci.yml'
            );
            $this->output->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                 'app/config',
                'parameters.gitlab-ci.yml'
            ));
        }
    }

    public function getRequireComposerPackages()
    {
        return [];
    }
}
