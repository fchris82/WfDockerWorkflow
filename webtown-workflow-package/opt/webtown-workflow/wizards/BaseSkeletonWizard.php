<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace Wizards;

use App\DependencyInjection\Compiler\TwigExtendingPass;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\InvalidComposerVersionNumber;
use App\Skeleton\BuilderTrait;
use App\Skeleton\FileType\SkeletonFile;
use App\Skeleton\SkeletonManagerTrait;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BaseSkeleton.
 *
 * "Fájlmásolós" wizard. Egy skeleton alapján dekorlálja a létező projektet, vagy éppen létrehoz egy újat.
 */
abstract class BaseSkeletonWizard extends BaseWizard
{
    use SkeletonManagerTrait;
    use BuilderTrait;

    /**
     * @var array
     */
    protected $workflowConfigurationCache;

    /**
     * BaseSkeleton constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param \Twig_Environment        $twig
     * @param Filesystem               $filesystem
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, \Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->twig = $twig;
        $this->fileSystem = $filesystem;
        $this->twigSkeletonNamespace = TwigExtendingPass::WIZARD_TWIG_NAMESPACE;
        parent::__construct($eventDispatcher);
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        return [];
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws \App\Exception\WizardHasAlreadyBuiltException
     * @throws \Exception
     */
    public function initBuild(BuildWizardEvent $event)
    {
        parent::initBuild($event);

        $event->setSkeletonVars($this->getSkeletonVars($event));

        $this->printHeader($event);
        $this->doBuildFiles($event);
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function doBuildFiles(BuildWizardEvent $event)
    {
        $skeletonFiles = $this->buildSkeletonFiles($event->getSkeletonVars());
        $this->dumpSkeletonFiles($skeletonFiles);
    }

    protected function printHeader(BuildWizardEvent $event)
    {
        $this->output->writeln("\n <comment>⏲</comment> <info>Start build...</info>\n");

        $table = new Table($this->output);
        $table
            ->setHeaders(['Placeholder', 'Value']);
        foreach ($event->getSkeletonVars() as $key => $value) {
            $table->addRow([
                $key,
                is_array($value) || is_object($value)
                    ? json_encode($value, JSON_PRETTY_PRINT)
                    : $value,
            ]);
        }
        $table->render();
    }

    protected function getWorkflowConfiguration($workingDirectory)
    {
        if (is_null($this->workflowConfigurationCache)) {
            $wfFiles = [
                '.wf.base.yml',
                '.wf.yml.dist',
                '.wf.yml',
            ];
            foreach ($wfFiles as $wfFile) {
                $configFilePath = $workingDirectory . '/' . $wfFile;
                if ($this->fileSystem->exists($configFilePath)) {
                    $this->workflowConfigurationCache = Yaml::parse(file_get_contents($configFilePath));
                    break;
                }
            }
            if (is_null($this->workflowConfigurationCache)) {
                throw new FileNotFoundException(sprintf(
                    'We couldn\'t find any WF configuration yaml file (or they are empty): `%s`! (Directory: %s)',
                    implode('`, `', $wfFiles),
                    $workingDirectory
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
     * @param string $workingDirectory
     * @param string $packageName
     * @param bool $allowAsk
     *
     * @return string
     */
    protected function getComposerPackageVersion($workingDirectory, $packageName, $allowAsk = true)
    {
        try {
            $composerLockPath = $workingDirectory . '/composer.lock';
            if (!$this->fileSystem->exists($composerLockPath)) {
                $composerJsonPath = $workingDirectory . '/composer.json';
                if ($this->fileSystem->exists($composerJsonPath)) {
                    $requires = $this->getComposerJsonInformation($workingDirectory, 'require', []);
                    if (array_key_exists($packageName, $requires)) {
                        $version = $requires[$packageName];

                        return $this->readComposerVersion($version);
                    }
                    $devRequires = $this->getComposerJsonInformation($workingDirectory, 'require-dev', []);
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
                    throw new FileNotFoundException(sprintf('The composer.lock and composer.json don\'t exist in the %s directory!', $workingDirectory));
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
        if (!$this->fileSystem->exists($composerJsonPath)) {
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
            return $this->fileSystem->exists($targetProjectDirectory . '/' . $this->getBuiltCheckFile());
        }

        return false;
    }

    protected function getBuiltCheckFile()
    {
        return null;
    }

    protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event) {}
    protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $preBuildSkeletonFileEvent) {}
    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent) {}
    protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event) {}

    protected function eventBeforeDumpFile(DumpFileEvent $event)
    {
        if ($this->isWfConfigYamlFile($event->getSkeletonFile())) {
            $content = $event->getSkeletonFile()->getContents();
            $helpComment = <<<EOS
# Available configuration parameters
# ==================================
#
# List all:
#   wf --config-dump
#
# List only names:
#   wf --config-dump --only-recipes
#
# List only a recipe:
#   wf --config-dump --recipe=symfony3
#
# Save to a file to edit:
#    wf --config-dump --no-ansi > .wf.yml
#
# Add new recipe:
#    wf --config-dump --recipe=php --no-ansi >> .wf.yml
#
# ----------------------------------------------------------------------------------------------------------------------

EOS;
            $event->getSkeletonFile()->setContents($helpComment . $content);
        }
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event) {}
    protected function eventAfterDumpFile(DumpFileEvent $event)
    {
        $this->printDumpedFile($event);
    }
    protected function eventSkipDumpFile(DumpFileEvent $event) {}

    protected function printDumpedFile(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        $status = $skeletonFile->getHandleExisting() == SkeletonFile::HANDLE_EXISTING_APPEND
            ? 'modified'
            : 'created'
        ;

        $this->output->writeln(sprintf(
            '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been %s.</info>',
            $skeletonFile->getRelativePath(),
            $skeletonFile->getFileName(),
            $status
        ));
    }

    protected function isWfConfigYamlFile(SkeletonFile $skeletonFile)
    {
        $filename = $skeletonFile->getFileName();
        $extension = $skeletonFile->getBaseFileInfo()->getExtension();

        if (strpos($filename, '.wf') !== 0) {
            return false;
        }

        if (in_array($extension, ['yml', 'yaml'])
            || substr($filename, -9) == '.yml.dist'
            || substr($filename, -10) == '.yaml.dist'
        ) {
            return true;
        }

        return false;
    }
}
