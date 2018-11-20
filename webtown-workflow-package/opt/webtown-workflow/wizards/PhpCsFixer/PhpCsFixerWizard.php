<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:35
 */

namespace Wizards\PhpCsFixer;

use App\Exception\WizardWfIsRequiredException;
use Wizards\BaseSkeletonWizard;

class PhpCsFixerWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'PhpCsFixer install';
    }

    public function getInfo()
    {
        return 'Add PhpCsFixer to the project.';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    public function checkRequires($targetProjectDirectory)
    {
        if (!file_exists($targetProjectDirectory . '/composer.json')) {
            throw new WizardSomethingIsRequiredException(sprintf('Initialized composer is required for this!'));
        }
        if (!$this->wfIsInitialized($targetProjectDirectory)) {
            throw new WizardWfIsRequiredException($this, $targetProjectDirectory);
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    protected function getBuiltCheckFile()
    {
        return '.php_cs.dist';
    }

    /**
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory)
    {
        $this->run(sprintf('cd %s && wf composer require --dev friendsofphp/php-cs-fixer', $targetProjectDirectory));
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
        $append = [
            '.gitignore',
        ];
        switch (true) {
            case in_array($relativePathName, $append):
                $this->filesystem->appendToFile($targetPath, $fileContent);
                break;
            default:
                parent::doWriteFile($targetPath, $fileContent, $relativePathName, $permission);
        }
    }
}
