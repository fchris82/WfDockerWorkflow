<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 15:55
 */

namespace App\Tests\Wizard;

use App\Tests\Dummy\Filesystem;
use App\Tests\Tool\BaseSkeletonTestCase;
use App\Wizards\BaseSkeletonWizard;
use PHPUnit\Framework\SkippedTestError;

class BaseSkeletonWizardTest extends BaseSkeletonTestCase
{
    /**
     * @param $initDir
     * @param $responseDir
     *
     * @throws ProjectHasDecoratedException
     *
     * @dataProvider getDirs
     */
    public function testBuild($skeletonDir, $initDir, $responseDir)
    {
        // @todo (Chris) Ezt még meg kellene írni normálisan
        throw new SkippedTestError('Deprecated');
        $filesystem = new Filesystem($initDir);
        $twig = $this->getTwig($initDir);
        $skeleton = new TestSkeletonWizard($twig, $filesystem);
        $skeleton->setSkeletonTemplateDirectory($skeletonDir);
        $this->initSkeleton($skeleton, []);

        $skeleton->runBuild($initDir);

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
     * @expectedException \App\Exception\ProjectHasDecoratedException
     */
    public function testBuilt($initDir, $responseDir)
    {
        $filesystem = new Filesystem($initDir);
        $twig = $this->getTwig($initDir);
        $skeleton = new TestSkeletonWizard($twig, $filesystem);
        $this->initSkeleton($skeleton, []);

        $skeleton->runBuild($initDir);
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
    protected function readSkeletonVars($targetProjectDirectory)
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

    public function getDefaultName()
    {
        return 'Test name';
    }

    protected function build($targetProjectDirectory)
    {
        // TODO: Implement build() method.
    }
}
