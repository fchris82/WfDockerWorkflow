<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:37
 */

namespace App\Webtown\WorkflowBundle\Environment\MicroParser;

use App\Webtown\WorkflowBundle\Exception\InvalidComposerVersionNumber;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class ComposerInstalledVersionParser extends ComposerJsonInformationParser
{
    protected $composerLockConfig = [];

    /**
     * Read the installed version number of a package:
     *
     *  1. Try to find in `composer.lock`
     *  2. Try to find in `composer.json` (require)
     *  3. Try to find in `composer.json` (require-dev)
     *  4. [$allowNoExists == true]: Ask the user
     *
     * @param string $workingDirectory
     * @param string $packageName
     *
     * @throws InvalidComposerVersionNumber
     *
     * @return string
     */
    public function get($workingDirectory, $packageName, $default = false)
    {
        try {
            $lockData = $this->getComposerLockConfig($workingDirectory);
            foreach ($lockData['packages'] as $package) {
                if ($package['name'] == $packageName) {
                    $version = $package['version'];

                    return $this->readComposerVersion($version);
                }
            }
            foreach ($lockData['packages-dev'] as $package) {
                if ($package['name'] == $packageName) {
                    $version = $package['version'];

                    return $this->readComposerVersion($version);
                }
            }
        } catch (FileNotFoundException $e) {
            $requires = parent::get($workingDirectory, 'require', []);
            if (array_key_exists($packageName, $requires)) {
                $version = $requires[$packageName];

                return $this->readComposerVersion($version);
            }
            $devRequires = parent::get($workingDirectory, 'require-dev', []);
            if (array_key_exists($packageName, $devRequires)) {
                $version = $devRequires[$packageName];

                return $this->readComposerVersion($version);
            }
        }

        return $default;
    }

    /**
     * @param $workingDirectory
     *
     * @throws FileNotFoundException
     *
     * @return mixed
     */
    protected function getComposerLockConfig($workingDirectory)
    {
        if (!array_key_exists($workingDirectory, $this->composerLockConfig)) {
            $composerJsonPath = $workingDirectory . '/composer.lock';
            if (!$this->fileSystem->exists($composerJsonPath)) {
                throw new FileNotFoundException(sprintf(
                    'The composer.lock doesn\'t exist in the %s directory!',
                    $workingDirectory
                ));
            }

            $this->composerJsonConfig[$workingDirectory] = json_decode(file_get_contents($composerJsonPath), true);
        }

        return $this->composerJsonConfig[$workingDirectory];
    }
}
