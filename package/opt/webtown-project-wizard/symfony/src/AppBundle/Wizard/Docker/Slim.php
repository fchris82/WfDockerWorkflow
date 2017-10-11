<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 16:47.
 */

namespace AppBundle\Wizard\Docker;

use AppBundle\Wizard\PublicWizardInterface;

/**
 * Class Slim.
 *
 * Lapos könyvtárstruktúrát húz rá a projektre.
 *
 * <code>
 *  DockerProjectSlim
 *  ├── .docker     <-- Docker fájlok
 *  │   ├── .data
 *  │   │   └── .gitkeep
 *  │   │
 *  │   ├── engine
 *  │   │   ├── config
 *  │   │   │   └── php_requires.txt
 *  │   │   ├── Dockerfile.dist
 *  │   │   ├── .gitignore
 *  │   │   └── entrypoint.sh
 *  │   ├── nginx
 *  │   │   └── vhost.conf
 *  │   │
 *  │   ├── docker-compose.local.yml.dist
 *  │   └── docker-compose.yml
 *  │
 *  └── [...]       <-- Projekt többi fájlja
 * </code>
 */
class Slim extends BaseDocker implements PublicWizardInterface
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
        return ['DockerProjectBase', 'DockerProjectSlim'];
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $defaults = [
            'project_directory'     => '.',
            'docker_data_dir'       => '.docker/.data',
            'docker_provisioning'   => '.docker',
            'deploy_directory'      => '.',
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
        switch (strtolower($relativePathName)) {
            case '.gitignore':
            case 'readme.md':
                $this->filesystem->appendToFile($targetPath, $fileContent);
                break;
            default:
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
    public function getComposerPackages()
    {
        return [];
    }

    public function getName()
    {
        return 'Docker - Slim';
    }

    public function getInfo()
    {
        return 'Create a slim docker project';
    }
}
