<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:51
 */

namespace Wizards\DockerProject;
use App\Wizard\WizardInterface;
use Wizards\Docker\BaseDocker;

/**
 * Class DockerProject.
 *
 * Lapos könyvtárstruktúrát húz rá a projektre.
 *
 * <code>
 *  DockerProject
 *  ├── [...]
 *  │
 *  └── .wf.yml.dist
 * </code>
 */
class DockerProjectWizard extends BaseDocker implements WizardInterface
{
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
    protected function getBuiltCheckFile()
    {
        return '.wf.yml.dist';
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $defaults = [
            'project_directory'     => '.',
            'deploy_directory'      => '.',
            'project_name'          => basename($this->getEnv('ORIGINAL_PWD', $targetProjectDirectory)),
            'current_wf_version'    => $this->input->getOption('wf-version'),
        ];

        return array_merge($variables, $defaults);
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
        switch (strtolower($relativePathName)) {
            case '.gitignore':
            case 'readme.md':
                $this->filesystem->appendToFile($targetPath, $fileContent);
                break;
            default:
                $this->filesystem->dumpFile($targetPath, $fileContent);

                if ($permission) {
                    $this->filesystem->chmod($targetPath, $permission);
                }
        }
    }

    public function getDefaultName()
    {
        return 'Docker Project';
    }

    public function getInfo()
    {
        return 'Create a dockered project';
    }

    public function getDefaultGroup()
    {
        return 'WF';
    }

    protected function build($targetProjectDirectory)
    {
        // TODO: Implement build() method.
    }
}
