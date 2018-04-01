<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.14.
 * Time: 14:29
 */

namespace AppBundle\Wizard\Docker;

use AppBundle\Wizard\BaseSkeletonWizard;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
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
 * @package AppBundle\Wizard\Docker
 */
abstract class BaseDocker extends BaseSkeletonWizard
{
    /**
     * @param string $targetProjectDirectory
     *
     * @return array
     */
    abstract protected function addVariables($targetProjectDirectory, $variables);

    protected function doBuildFiles($targetProjectDirectory, $templateVariables)
    {
        $finder = $this->getTemplatesFinder($targetProjectDirectory);
        $finder->exclude('docker-content');
        foreach ($finder as $templateFile) {
            if ($templateFile->getFilename() == 'docker-content') {
                $this->dockerContentCopy($targetProjectDirectory, $templateFile->getRelativePath(), $templateVariables);
            } else {
                $targetPath = $this->doBuildFile($targetProjectDirectory, $templateFile, $templateVariables);
                $this->output->writeln(sprintf(
                    '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                    $targetPath->getRelativePath(),
                    $targetPath->getFilename()
                ));
            }
        }
    }

    protected function dockerContentCopy($targetProjectDirectory, $subPath, $templateVariables)
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->getSkeletonTemplateDirectoryFull('DockerProjectBase/docker-content'))
            ->ignoreDotFiles(false)
        ;

        foreach ($finder as $file) {
            $templateFile = new SplFileInfo(
                $file->getPathname(),
                'docker-content' . DIRECTORY_SEPARATOR . $file->getRelativePath(),
                'docker-content' . DIRECTORY_SEPARATOR . $file->getRelativePathname()
            );
            $fileContent = $this->parseTemplateFile($templateFile, $templateVariables);

            $targetPath = implode(DIRECTORY_SEPARATOR, [
                rtrim($targetProjectDirectory, DIRECTORY_SEPARATOR),
                $subPath,
                $file->getRelativePathname(),
            ]);
            $this->doWriteFile(
                $targetPath,
                $fileContent,
                $file->getRelativePathname(),
                // Az .sh-ra végződő fájloknál adunk futási jogot
                substr($targetPath, -3) == '.sh' || (fileperms($templateFile->getPathname()) & 0700 === 0700)
                    ? 0755
                    : null
            );

            $targetPathInfo = new SplFileInfo($targetPath, $file->getRelativePath(), $file->getRelativePathname());

            $this->output->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                rtrim($subPath . DIRECTORY_SEPARATOR . $targetPathInfo->getRelativePath(), DIRECTORY_SEPARATOR),
                $targetPathInfo->getFilename()
            ));
        }
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function setVariables($targetProjectDirectory)
    {
        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.1</info>]', '7.1');
        $variables['php_version'] = $this->ask($phpVersionQuestion);

        // Megpróbáljuk kiolvasni a használt SF verziót, már ha létezik
        try {
            $symfonyVersion = $this->getComposerPackageVersion($targetProjectDirectory, 'symfony/symfony');
        } catch (\Exception $e) {
            $symfonyVersion = false;
        }

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
                $variables['sf_bin_dir']     = 'vendor/bin';
                $variables['shared_dirs']    = 'var';
                $variables['web_directory']  = 'public';
                $variables['index_file']     = 'index.php';
                break;
            case '3.':
                $variables['sf_version']     = 3;
                $variables['sf_console_cmd'] = 'bin/console';
                $variables['sf_bin_dir']     = 'vendor/bin';
                $variables['shared_dirs']    = 'var';
                $variables['web_directory']  = 'web';
                $variables['index_file']     = 'app.php';
                break;
            case '2.':
                $variables['sf_version']     = 2;
                $variables['sf_console_cmd'] = 'app/console';
                $variables['sf_bin_dir']     = 'bin';
                $variables['shared_dirs']    = 'app/cache app/logs';
                $variables['web_directory']  = 'web';
                $variables['index_file']     = 'app.php';
                break;
            default:
                throw new \InvalidArgumentException('Invalid selection! Missiong settings!');
        }

        $dockerRepoQuestion = new Question('Set the engine docker repository [<info>amapa.webtown.hu:5000</info>]', 'amapa.webtown.hu:5000');
        $variables['docker_engine_repo_base'] = $this->ask($dockerRepoQuestion);
        $defaultRepoName = basename($this->getEnv('ORIGINAL_PWD', '???')) . '-engine';
        $dockerRepoNameQuestion = new Question(sprintf(
            'Set the engine docker repository name [%s/<info>%s</info>]',
            $variables['docker_engine_repo_base'],
            $defaultRepoName
        ), $defaultRepoName);
        $variables['docker_engine_repo_name'] = $this->ask($dockerRepoNameQuestion);

        // Az eltérő változók bekérése vagy betöltése
        $variables = $this->addVariables($targetProjectDirectory, $variables);

        return $variables;
    }
}
