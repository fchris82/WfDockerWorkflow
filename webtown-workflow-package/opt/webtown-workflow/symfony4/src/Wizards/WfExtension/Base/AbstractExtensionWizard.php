<?php

namespace App\Wizards\WfExtension\Base;

use App\Webtown\WorkflowBundle\Environment\Commander;
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

abstract class AbstractExtensionWizard extends BaseSkeletonWizard
{
    const WF_CHECK_DIRECTORY = 'webtown-workflow-package/opt/webtown-workflow';

    const EXTENSION_DIRECTORY_NAME = 'extensions';

    /**
     * @var string
     */
    protected $hostConfigurationPath;

    public function __construct(
        $hostConfigurationPath,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        $this->hostConfigurationPath = $hostConfigurationPath;
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
    }

    protected function workingDirectoryIsWfDevOrHostConfiguration($directory)
    {
        return file_exists($directory . \DIRECTORY_SEPARATOR . static::WF_CHECK_DIRECTORY)
            || basename($directory) == basename($this->hostConfigurationPath);
    }

    protected function workingDirectoryIsAnExtension($directory)
    {
        $composerPath = $directory . \DIRECTORY_SEPARATOR . 'composer.json';
        // composer.json exists
        if (!file_exists($composerPath)) {
            return false;
        }
        $composerConfig = json_decode(file_get_contents($composerPath), true);

        // `wf-extension` keyword exists in the composer.json file
        return array_key_exists('keywords', $composerConfig)
            && in_array('wf-extension', $composerConfig['keywords'])
            && $composerConfig['type'] == 'library';
    }

    protected function createSymlink($workingDirectory, $groupDirectory, $namespace)
    {
        $original = $workingDirectory . \DIRECTORY_SEPARATOR . $groupDirectory . \DIRECTORY_SEPARATOR . $namespace;
        $shFile = $_ENV['HOME'] . '/bin/wfdev';
        if (!$this->fileSystem->exists($shFile)) {
            return false;
        }

        $shContents = file_get_contents($shFile);
        if (!preg_match('# (/.*)/webtown-workflow-package/opt/webtown-workflow/host/bin/workflow_runner.sh#', $shContents, $matches)) {
            return false;
        }

        $clonedWfPath = $matches[1];
        $target = $clonedWfPath . \DIRECTORY_SEPARATOR
            . 'webtown-workflow-package/opt/webtown-workflow/symfony4/src' . \DIRECTORY_SEPARATOR
            . $groupDirectory . \DIRECTORY_SEPARATOR
            . $namespace;
        $this->fileSystem->symlink(
            $original,
            $target
        );

        return $target;
    }
}
