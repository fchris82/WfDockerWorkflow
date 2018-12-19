<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:37
 */

namespace App\Webtown\WorkflowBundle\Environment\MicroParser;

use App\Webtown\WorkflowBundle\Exception\InvalidComposerVersionNumber;
use App\Webtown\WorkflowBundle\Exception\ValueIsMissingException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;

class ComposerJsonInformationParser implements MicroParserInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var array
     */
    protected $composerJsonConfig = [];

    /**
     * ComposerInstalledVersionParser constructor.
     *
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    public function get($workingDirectory, $infoPath, $default = false)
    {
        $keys = explode('.', $infoPath);
        $current = $this->getComposerJsonConfig($workingDirectory);
        foreach ($keys as $key) {
            if (!\is_array($current) || !array_key_exists($key, $current)) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }

    /**
     * @param $workingDirectory
     *
     * @throws FileNotFoundException
     *
     * @return mixed
     */
    protected function getComposerJsonConfig($workingDirectory)
    {
        if (!array_key_exists($workingDirectory, $this->composerJsonConfig)) {
            $composerJsonPath = $workingDirectory . '/composer.json';
            if (!$this->fileSystem->exists($composerJsonPath)) {
                throw new FileNotFoundException(sprintf(
                    'The composer.json doesn\'t exist in the %s directory!',
                    $workingDirectory
                ));
            }

            $this->composerJsonConfig[$workingDirectory] = json_decode(file_get_contents($composerJsonPath), true);
        }

        return $this->composerJsonConfig[$workingDirectory];
    }

    public function has($workingDirectory, $infoPath)
    {
        $value = $this->get($workingDirectory, $infoPath, new ValueIsMissingException());

        return !$value instanceof ValueIsMissingException;
    }

    /**
     * @param $versionText
     *
     * @throws InvalidComposerVersionNumber
     *
     * @return string
     */
    public function readComposerVersion($versionText)
    {
        if (preg_match('{[\d\.]+}', $versionText, $matches)) {
            return $matches[0];
        }

        throw new InvalidComposerVersionNumber($versionText);
    }
}
