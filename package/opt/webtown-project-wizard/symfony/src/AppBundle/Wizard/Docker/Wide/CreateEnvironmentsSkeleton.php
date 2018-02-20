<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 16:47.
 */

namespace AppBundle\Wizard\Docker\Wide;

use AppBundle\Wizard\Docker\BaseDocker;

class CreateEnvironmentsSkeleton extends BaseDocker
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
        return '.project.env.dist';
    }

    /**
     * A skeleton fájlok helye.
     *
     * @return string|array
     */
    protected function getSkeletonTemplateDirectory()
    {
        return ['DockerProjectBase', 'DockerProjectWide'];
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $defaults = [
            'project_directory'     => 'project',
            'docker_data_dir'       => 'equipment/.data',
            'docker_provisioning'   => 'equipment/dev',
            'deploy_directory'      => 'deploy',
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
     * @param $targetPath
     * @param $fileContent
     * @param $relativePathName
     */
    protected function doWriteFile($targetPath, $fileContent, $relativePathName)
    {
        switch ($relativePathName) {
            case MoveProjectFiles::TARGET_DIRECTORY . DIRECTORY_SEPARATOR . '.gitkeep':
                break;
            case '.gitignore':
                $this->filesystem->appendToFile($targetPath, $fileContent);
                break;
            default:
                // @todo (Chris) Ezt átírni intelligensre, hogy rákérdez, ha felülírna egy másik fájlt.
                $this->filesystem->dumpFile($targetPath, $fileContent);
        }
    }

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
    public function getRequireComposerPackages()
    {
        return [];
    }

    public function getName()
    {
        return "Create docker wide environment";
    }
}
