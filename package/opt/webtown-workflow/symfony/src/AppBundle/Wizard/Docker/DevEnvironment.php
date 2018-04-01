<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.02.20.
 * Time: 16:47
 */

namespace AppBundle\Wizard\Docker;

use AppBundle\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class DevEnvironment.
 *
 * Egy git repo-n kívüli használható környezetet hoz létre. Ez használható, ha pl létre kell hozni egy környezetet egy
 * third party bundle-nek, és szükség van php-re, composer-re, vagy más dologra.
 *
 * <code>
 *  DockerProjectSlim
 *  ├── .docker.env    <-- Docker fájlok
 *  │   ├── .data
 *  │   │   └── .gitkeep
 *  │   │
 *  │   ├── engine
 *  │   │   ├── config
 *  │   │   │   └── php_requires.txt
 *  │   │   ├── Dockerfile
 *  │   │   ├── entrypoint.sh
 *  │   │   └── wait-for-it.sh
 *  │   │
 *  │   └── docker-compose.yml
 *  │
 *  └── [...]       <-- Projekt többi fájlja
 * </code>
 */
class DevEnvironment extends BaseDocker implements PublicWizardInterface
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
        return '.docker.env.makefile';
    }

    /**
     * A skeleton fájlok helye.
     *
     * @return string|array
     */
    protected function getSkeletonTemplateDirectory()
    {
        return ['DockerDevEnvironment'];
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function setVariables($targetProjectDirectory)
    {
        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.1</info>]', '7.1');
        $variables['php_version'] = $this->ask($phpVersionQuestion);

        // Az eltérő változók bekérése vagy betöltése
        $variables = $this->addVariables($targetProjectDirectory, $variables);

        return $variables;
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $defaults = [
            'project_directory'     => '.',
            'docker_data_dir'       => '.docker.env/.data',
            'docker_provisioning'   => '.docker.env',
        ];

        return array_merge($variables, $defaults);
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
        return 'Docker Environment - Git outside';
    }

    public function getInfo()
    {
        return 'Create a docker environment for a project, hidden from git. You have to register the <info>/.docker.env</info>' .
            ' and the <info>/.docker.env.makefile</info> in your <comment>global .gitignore</comment> file! You must use this' .
            ' with third party bundles or other components.';
    }
}
