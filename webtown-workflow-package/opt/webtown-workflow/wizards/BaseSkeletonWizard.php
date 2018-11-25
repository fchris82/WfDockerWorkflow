<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace Wizards;

use App\DependencyInjection\Compiler\TwigExtendingPass;
use App\Exception\InvalidComposerVersionNumber;
use App\Skeleton\ExecutableSkeletonFile;
use App\Skeleton\SkeletonFile;
use App\Skeleton\SkeletonManagerTrait;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BaseSkeleton.
 *
 * "Fájlmásolós" wizard. Egy skeleton alapján dekorlálja a létező projektet, vagy éppen létrehoz egy újat.
 */
abstract class BaseSkeletonWizard extends BaseWizard
{
    use SkeletonManagerTrait;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $workflowConfigurationCache;

    /**
     * BaseSkeleton constructor.
     *
     * @param \Twig_Environment $twig
     * @param Filesystem        $filesystem
     */
    public function __construct(\Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->filesystem = $filesystem;
        $this->twigSkeletonNamespace = TwigExtendingPass::WIZARD_TWIG_NAMESPACE;
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
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
        $this->filesystem->dumpFile($targetPath, $fileContent);

        if ($permission) {
            $this->filesystem->chmod($targetPath, $permission);
        }
    }

    /**
     * @param string $targetProjectDirectory
     *
     * @return string
     *
     * @throws \App\Exception\WizardHasAlreadyBuiltException
     * @throws \Exception
     */
    public function initBuild($targetProjectDirectory)
    {
        parent::initBuild($targetProjectDirectory);

        $templateVariables = $this->setVariables($targetProjectDirectory);
        $this->printHeader($templateVariables);
        $this->doBuildFiles($targetProjectDirectory, $templateVariables);

        return $targetProjectDirectory;
    }

    /**
     * @param $targetProjectDirectory
     * @param $templateVariables
     *
     * @throws \Exception
     */
    protected function doBuildFiles($targetProjectDirectory, $templateVariables)
    {
        /** @var SkeletonFile $skeletonFile */
        foreach ($this->buildSkeletonFiles($templateVariables) as $skeletonFile) {
            $targetPath = $this->doBuildFile($targetProjectDirectory, $skeletonFile);
            $this->output->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                $targetPath->getRelativePath(),
                basename($targetPath->getRelativePathname())
            ));
        }
    }

    /**
     * @param $targetProjectDirectory
     * @param SkeletonFile $skeletskeletonFile
     *
     * @return SplFileInfo
     */
    protected function doBuildFile($targetProjectDirectory, SkeletonFile $skeletskeletonFile)
    {
        $targetPath = implode(DIRECTORY_SEPARATOR, [
            rtrim($targetProjectDirectory, DIRECTORY_SEPARATOR),
            $skeletskeletonFile->getRelativePathname(),
        ]);
        $this->doWriteFile(
            $targetPath,
            $skeletskeletonFile->getContents(),
            $skeletskeletonFile->getRelativePathname(),
            // Az .sh-ra végződő vagy futási joggal rendelkező fájloknál adunk futási jogot
            $skeletskeletonFile instanceof ExecutableSkeletonFile ?
                $skeletskeletonFile->getPermission() :
                $skeletskeletonFile->getBaseFileInfo()->getPerms()
        );

        return $skeletskeletonFile->getBaseFileInfo();
    }

    protected function printHeader($templateVariables)
    {
        $this->output->writeln("\n <comment>⏲</comment> <info>Start build...</info>\n");

        $table = new Table($this->output);
        $table
            ->setHeaders(['Placeholder', 'Value']);
        foreach ($templateVariables as $key => $value) {
            $table->addRow([
                $key,
                is_array($value) || is_object($value)
                    ? json_encode($value, JSON_PRETTY_PRINT)
                    : $value,
            ]);
        }
        $table->render();
    }

    protected function getWorkflowConfiguration($targetDirectory)
    {
        if (is_null($this->workflowConfigurationCache)) {
            $wfFiles = [
                '.wf.base.yml',
                '.wf.yml.dist',
                '.wf.yml',
            ];
            foreach ($wfFiles as $wfFile) {
                $configFilePath = $targetDirectory . '/' . $wfFile;
                if ($this->filesystem->exists($configFilePath)) {
                    $this->workflowConfigurationCache = Yaml::parse(file_get_contents($configFilePath));
                    break;
                }
            }
            if (is_null($this->workflowConfigurationCache)) {
                throw new FileNotFoundException(sprintf(
                    'We couldn\'t find any WF configuration yaml file (or they are empty): `%s`! (Directory: %s)',
                    implode('`, `', $wfFiles),
                    $targetDirectory
                ));
            }
        }

        return $this->workflowConfigurationCache;
    }

    /**
     * Read the installed version number of a package:
     *
     *  1. Try to find in `composer.lock`
     *  2. Try to find in `composer.json` (require)
     *  3. Try to find in `composer.json` (require-dev)
     *  4. [$allowNoExists == true]: Ask the user
     *
     * @param string $targetDirectory
     * @param string $packageName
     * @param bool $allowAsk
     *
     * @return string
     */
    protected function getComposerPackageVersion($targetDirectory, $packageName, $allowAsk = true)
    {
        try {
            $composerLockPath = $targetDirectory . '/composer.lock';
            if (!$this->filesystem->exists($composerLockPath)) {
                $composerJsonPath = $targetDirectory . '/composer.json';
                if ($this->filesystem->exists($composerJsonPath)) {
                    $requires = $this->getComposerJsonInformation($targetDirectory, 'require', []);
                    if (array_key_exists($packageName, $requires)) {
                        $version = $requires[$packageName];

                        return $this->readComposerVersion($version);
                    }
                    $devRequires = $this->getComposerJsonInformation($targetDirectory, 'require-dev', []);
                    if (array_key_exists($packageName, $devRequires)) {
                        $version = $devRequires[$packageName];

                        return $this->readComposerVersion($version);
                    }

                    if ($allowAsk) {
                        $versionQuestion = new Question(sprintf(
                            'We don\'t find the <info>%s</info> package in composer.json file and the composer.lock hasn\'t created yet. Please set the version manually: ',
                            $packageName
                        ));
                        $version = $this->ask($versionQuestion);

                        return $this->readComposerVersion($version);
                    }
                } else {
                    throw new FileNotFoundException(sprintf('The composer.lock and composer.json don\'t exist in the %s directory!', $targetDirectory));
                }
            } else {
                $composer = json_decode(file_get_contents($composerLockPath), true);
                foreach ($composer['packages'] as $package) {
                    if ($package['name'] == $packageName) {
                        $version = $package['version'];

                        return $this->readComposerVersion($version);
                    }
                }
            }
        } catch (InvalidComposerVersionNumber $e) {
            if ($allowAsk) {
                $versionQuestion = new Question(sprintf(
                    'We need the version of the <info>%s</info> package but we got invalid version number string (<info>%s</info>). Please set the version manually:  ',
                    $packageName,
                    $e->getVersion()
                ));
                $version = $this->ask($versionQuestion);

                return $this->readComposerVersion($version);
            }
        }

        return false;
    }

    protected function readComposerVersion($versionText)
    {
        if (preg_match('{[\d\.]+}', $versionText, $matches)) {
            return $matches[0];
        }

        throw new InvalidComposerVersionNumber($versionText);
    }

    /**
     * Different projects and versions contains different packages, so we need to check more then one option.
     *
     * @param $targetDirectory
     *
     * @return bool|string
     */
    protected function getSymfonyVersion($targetDirectory)
    {
        $symfonyPackages = [
            'symfony/symfony',
            'symfony/config',
        ];
        foreach ($symfonyPackages as $symfonyPackage) {
            $packageVersion = $this->getComposerPackageVersion($targetDirectory, $symfonyPackage, true);
            if ($packageVersion) {
                return $packageVersion;
            }
        }

        return false;
    }

    protected function getComposerJsonInformation($targetDirectory, $infoPath, $default = null)
    {
        $composerJsonPath = $targetDirectory . '/composer.json';
        if (!$this->filesystem->exists($composerJsonPath)) {
            throw new FileNotFoundException(sprintf('The composer.json doesn\'t exist in the %s directory!', $targetDirectory));
        }

        $data = json_decode(file_get_contents($composerJsonPath), true);
        $keys = explode('.', $infoPath);
        $current = $data;
        foreach ($keys as $key) {
            if (!is_array($current) || !array_key_exists($key, $current)) {
                return $default;
            }
            $current = $current[$key];
        }

        return $current;
    }

    protected function readSymfonyBinDir($targetDirectory, $default = null)
    {
        $byExtra = $this->getComposerJsonInformation($targetDirectory, 'extra.symfony-bin-dir');
        if ($byExtra) {
            return $byExtra;
        }

        $byConfig = $this->getComposerJsonInformation($targetDirectory, 'config.bin-path');

        return $byConfig ?: $default;
    }

    public function isBuilt($targetProjectDirectory)
    {
        if ($this->getBuiltCheckFile()) {
            return $this->filesystem->exists($targetProjectDirectory . '/' . $this->getBuiltCheckFile());
        }

        return false;
    }

    protected function getBuiltCheckFile()
    {
        return null;
    }
}
