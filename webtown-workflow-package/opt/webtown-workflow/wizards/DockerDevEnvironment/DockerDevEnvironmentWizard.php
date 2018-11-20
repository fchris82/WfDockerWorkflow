<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:46
 */

namespace Wizards\DockerDevEnvironment;
use App\Wizard\WizardInterface;
use Symfony\Component\Console\Question\Question;
use Wizards\Docker\BaseDocker;

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
 *  │   │   └── Dockerfile
 *  │   │
 *  │   └── docker-compose.yml
 *  │
 *  └── [...]       <-- Projekt többi fájlja
 * </code>
 */
class DockerDevEnvironmentWizard extends BaseDocker implements WizardInterface
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
        return '.wf.yml';
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
    protected function addVariables($targetProjectDirectory, $variables)
    {
        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.1</info>]', '7.2');
        $variables['php_version'] = $this->ask($phpVersionQuestion);
        $variables['project_name'] = basename($targetProjectDirectory);

        return $variables;
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

    public function getDefaultName()
    {
        return 'Docker Environment - Git outside';
    }

    public function getInfo()
    {
        return 'Create a docker environment for a project, hidden from git. You have to register the <info>/.docker.env</info>' .
            ' and the <info>/.docker.env.makefile</info> in your <comment>global .gitignore</comment> file! You must use this' .
            ' with third party bundles or other components.';
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
