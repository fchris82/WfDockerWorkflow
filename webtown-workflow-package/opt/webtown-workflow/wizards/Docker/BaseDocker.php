<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.14.
 * Time: 14:29
 */

namespace Wizards\Docker;

use Wizards\BaseSkeletonWizard;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @todo (Chris) A megjegyzést upgrade-elni, mert már jócskán elavult.
 *
 * Class BaseDocker
 *
 * A docker beállításokkal kapcsolatos fájlok megegyeznek, csak a célterület különböző. Az, hogy hova kell őket másolni,
 * egy "helyörzős" rendszerben lett lekezleve. Ide másolja a DockerProjectBase/docker-content könyvtár tartalmát:
 *
 *  skeletons
 *  ├── DockerProjectBase
 *  │   ├── [docker-content]                        <-- Ennek a könyvtárnak a tartalmát másolja át
 *  │   │   ├── engine                               <
 *  │   │   │   ├── config                           <
 *  │   │   │   │   └── php_requires.txt             <
 *  │   │   │   ├── Dockerfile-alpine-example.dist   <
 *  │   │   │   ├── Dockerfile.dist                  <
 *  │   │   │   ├── entrypoint.sh                    <
 *  │   │   │   └── .gitignore                       <
 *  │   │   ├── nginx                                <
 *  │   │   │   └── vhost.conf                       <
 *  │   │   ├── docker-compose.local.yml.dist        <
 *  │   │   └── docker-compose.yml                   <
 *  │   └── ...
 *  │
 *  ├── DockerProjectSlim
 *  │   └── .docker
 *  │       ├── .data
 *  │       │   └── .gitkeep
 *  │       └── docker-content      <-- IDE
 *  │
 *  └── DockerProjectWide
 *      ├── ...
 *      └── equipment
 *          ├── .data
 *          │   └── .gitkeep
 *          └── dev
 *              └── docker-content  <-- IDE
 *
 *
 * @package App\Wizard\Docker
 */
abstract class BaseDocker extends BaseSkeletonWizard
{
    /**
     * @param string $targetProjectDirectory
     *
     * @return array
     */
    abstract protected function addVariables($targetProjectDirectory, $variables);

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function getSkeletonVars($targetProjectDirectory)
    {
        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.2</info>]', '7.2');
        $variables['php_version'] = $this->ask($phpVersionQuestion);

        // Megpróbáljuk kiolvasni a használt SF verziót, már ha létezik
        $symfonyVersion = $this->getSymfonyVersion($targetProjectDirectory);

        if (!$symfonyVersion) {
            $symfonyVersionQuestion = new ChoiceQuestion(
                'Which symfony version do you want to use? [<info>4.*</info>]',
                ['4.*', '3.* (eZ project + LTE)', '2.* [deprecated]'],
                0
            );
            $symfonyVersion = $this->ask($symfonyVersionQuestion);
        }
        switch (substr($symfonyVersion, 0, 2)) {
            case '4.':
                $variables['sf_version']     = 4;
                $variables['sf_console_cmd'] = 'bin/console';
                $variables['sf_bin_dir']     = $this->readSymfonyBinDir($targetProjectDirectory, 'vendor/bin');
                $variables['shared_dirs']    = 'var';
                $variables['web_directory']  = 'public';
                $variables['index_file']     = 'index.php';
                break;
            case '3.':
                $variables['sf_version']     = 3;
                $variables['sf_console_cmd'] = 'bin/console';
                $variables['sf_bin_dir']     = $this->readSymfonyBinDir($targetProjectDirectory, 'vendor/bin');
                $variables['shared_dirs']    = 'var';
                $variables['web_directory']  = 'web';
                $variables['index_file']     = 'app.php';
                break;
            case '2.':
                $variables['sf_version']     = 2;
                $variables['sf_console_cmd'] = 'app/console';
                $variables['sf_bin_dir']     = $this->readSymfonyBinDir($targetProjectDirectory, 'bin');
                $variables['shared_dirs']    = 'app/cache app/logs';
                $variables['web_directory']  = 'web';
                $variables['index_file']     = 'app.php';
                break;
            default:
                throw new \InvalidArgumentException('Invalid selection! Missiong settings!');
        }

        // Az eltérő változók bekérése vagy betöltése
        $variables = $this->addVariables($targetProjectDirectory, $variables);

        return $variables;
    }
}
