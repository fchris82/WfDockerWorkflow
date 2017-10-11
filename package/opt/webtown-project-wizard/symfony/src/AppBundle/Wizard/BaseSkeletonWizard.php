<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace AppBundle\Wizard;

use AppBundle\Exception\ProjectHasDecoratedException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class BaseSkeleton.
 *
 * "Fájlmásolós" wizard. Egy skeleton alapján dekorlálja a létező projektet, vagy éppen létrehoz egy újat.
 */
abstract class BaseSkeletonWizard extends BaseWizard
{
    /**
     * Skeletons base dir.
     *
     * @var string
     */
    protected $baseDir;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * BaseSkeleton constructor.
     *
     * @param string            $baseDir
     * @param \Twig_Environment $twig
     * @param Filesystem        $filesystem
     */
    public function __construct($baseDir, \Twig_Environment $twig, Filesystem $filesystem)
    {
        $this->baseDir = $baseDir;
        $this->twig = $twig;
        $this->filesystem = $filesystem;
    }

    /**
     * A skeleton fájlok helye.
     *
     * @return string|array
     */
    abstract protected function getSkeletonTemplateDirectory();

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    abstract protected function setVariables($targetProjectDirectory);

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
     * @param $targetPath
     * @param $fileContent
     * @param $relativePathName
     */
    abstract protected function doWriteFile($targetPath, $fileContent, $relativePathName);

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
    abstract public function getComposerPackages();

    /**
     * Az itt visszaadott fájllal ellenőrizzük, hogy az adott dekorátor lefutott-e már.
     * <code>
     *  protected function getBuiltCheckFile() {
     *      return '.docker';
     *  }
     * </code>.
     *
     * @return string
     */
    abstract protected function getBuiltCheckFile();

    /**
     * Ellenőrzi, hogy a prjekt már az alábbival dekorálva lett-e már.
     *
     * @param $targetProjectDirectory
     *
     * @return bool
     */
    public function isBuilt($targetProjectDirectory)
    {
        $testDirectory = rtrim($targetProjectDirectory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . $this->getBuiltCheckFile();

        return $this->filesystem->exists($testDirectory);
    }

    public function build($targetProjectDirectory)
    {
        if ($this->isBuilt($targetProjectDirectory)) {
            throw new ProjectHasDecoratedException();
        }

        $templateVariables = $this->setVariables($targetProjectDirectory);
        $this->printHeader($templateVariables);
        $this->doBuildFiles($targetProjectDirectory, $templateVariables);

        return $targetProjectDirectory;
    }

    protected function doBuildFiles($targetProjectDirectory, $templateVariables)
    {
        foreach ($this->getTemplatesFinder() as $templateFile) {
            $targetPath = $this->doBuildFile($targetProjectDirectory, $templateFile, $templateVariables);
            $this->output->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                $targetPath->getRelativePath(),
                $targetPath->getFilename()
            ));
        }
    }

    /**
     * @param $targetProjectDirectory
     * @param SplFileInfo $templateFile
     * @param array       $templateVariables
     *
     * @return SplFileInfo
     */
    protected function doBuildFile($targetProjectDirectory, SplFileInfo $templateFile, array $templateVariables)
    {
        $fileContent = $this->parseTemplateFile($templateFile, $templateVariables);

        $targetPath = implode(DIRECTORY_SEPARATOR, [
            rtrim($targetProjectDirectory, DIRECTORY_SEPARATOR),
            $templateFile->getRelativePathname(),
        ]);
        $this->doWriteFile($targetPath, $fileContent, $templateFile->getRelativePathname());

        return new SplFileInfo($targetPath, $templateFile->getRelativePath(), $templateFile->getRelativePathname());
    }

    protected function parseTemplateFile(SplFileInfo $templateFile, array $templateVariables)
    {
        $skeletonTemplateDirectory = basename(rtrim(
            str_replace(
                $templateFile->getRelativePathname(),
                '',
                $templateFile->getPathname()
            ), DIRECTORY_SEPARATOR . '/'));
        $file = sprintf('@skeleton/%s/%s', $skeletonTemplateDirectory, $templateFile->getRelativePathname());

        return $this->twig->render($file, $templateVariables);
    }

    protected function getSkeletonTemplateDirectoryFull($directoryName)
    {
        return rtrim($this->baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $directoryName;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return Finder
     */
    protected function getTemplatesFinder()
    {
        $directories = [];
        foreach ((array) $this->getSkeletonTemplateDirectory() as $directory) {
            $directories[] = $this->getSkeletonTemplateDirectoryFull($directory);
        }

        $finder = new Finder();
        $finder
            ->files()
            ->in($directories)
            ->ignoreDotFiles(false);

        return $finder;
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
}
