<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 15:55
 */

namespace AppBundle\Wizard;

use Tests\Dummy\Filesystem;
use Tests\Tool\BaseSkeletonTestCase;

class BaseSkeletonWizardTest extends BaseSkeletonTestCase
{
    /**
     * @param $initDir
     * @param $responseDir
     *
     * @dataProvider getDirs
     */
    public function testBuild($skeletonDir, $initDir, $responseDir)
    {
        $filesystem = new Filesystem($initDir);
        $twig = $this->getTwig($initDir);
        $skeleton = new TestSkeletonWizard($this->getBaseDir(), $twig, $filesystem);
        $skeleton->setSkeletonTemplateDirectory($skeletonDir);
        $this->initSkeleton($skeleton, []);

        $skeleton->build($initDir);

        $this->compareResults($responseDir, $initDir, $filesystem);
    }

    public function getDirs()
    {
        return [
            // Normál
            ['DockerProjectBase', __DIR__ . '/Resources/init/init1', __DIR__ . '/Resources/result/result1'],
            // Összetett
            [['DockerProjectBase', 'DockerProjectWide'], __DIR__ . '/Resources/init/init1', __DIR__ . '/Resources/result/result2'],
        ];
    }

    /**
     * @param $initDir
     * @param $responseDir
     *
     * @dataProvider getBuiltDirs
     * @expectedException \AppBundle\Exception\ProjectHasDecoratedException
     */
    public function testBuilt($initDir, $responseDir)
    {
        $filesystem = new Filesystem($initDir);
        $twig = $this->getTwig($initDir);
        $skeleton = new TestSkeletonWizard($this->getBaseDir(), $twig, $filesystem);
        $this->initSkeleton($skeleton, []);

        $skeleton->build($initDir);
    }

    public function getBuiltDirs()
    {
        return [
            // Már futtattuk
            [__DIR__ . '/Resources/result/result1', __DIR__ . '/Resources/result/result1'],
        ];
    }
}

class TestSkeletonWizard extends BaseSkeletonWizard
{
    protected $skeletonTemplateDirectory;

    public function setSkeletonTemplateDirectory($dir)
    {
        $this->skeletonTemplateDirectory = $dir;

        return $this;
    }

    /**
     * A skeleton fájlok helye.
     *
     * @return string|array
     */
    protected function getSkeletonTemplateDirectory()
    {
        return $this->skeletonTemplateDirectory;
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
     * </code>
     *
     * @param $targetPath
     * @param $fileContent
     * @param $relativePathName
     */
    protected function doWriteFile($targetPath, $fileContent, $relativePathName)
    {
        $this->filesystem->dumpFile($targetPath, $fileContent);
    }

    /**
     * 'dev' => [... dev packages ...]
     * 'nodev' => [... nodev packages ...]
     *
     * Eg:
     * <code>
     *  return ['dev' => ["friendsofphp/php-cs-fixer:~2.3.3"]];
     * </code>
     *
     * @return array
     */
    public function getComposerPackages()
    {
        return [];
    }

    /**
     * Az itt visszaadott fájllal ellenőrizzük, hogy az adott dekorátor lefutott-e már.
     * <code>
     *  protected function getBuiltCheckFile() {
     *      return '.docker';
     *  }
     * </code>
     *
     * @return string
     */
    protected function getBuiltCheckFile()
    {
        return '.project.env.dist';
    }
}