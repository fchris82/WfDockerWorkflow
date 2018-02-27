<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.02.19.
 * Time: 11:52
 */

namespace AppBundle\Wizard\Symfony;

use AppBundle\Wizard\BaseWizard;
use AppBundle\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\Question;

class SymfonyComposerBuildWizard extends BaseWizard implements PublicWizardInterface
{
    protected $askDirectory = true;

    public function getName()
    {
        return 'Symfony builder';
    }

    public function getInfo()
    {
        return 'Create a symfony project';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }

    /**
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory)
    {
        $directoryQuestion = new Question('Add meg a könyvtárat, ahová szeretnéd telepíteni: ', '.');
        $versionQuestion = new Question('Add meg verziót [Üresen hagyva a legutóbbi stabil verziót szedi le, egyébként: <info>x.x</info>] ');

        $directory = $this->askDirectory
            ? $this->ask($directoryQuestion)
            : '.';
        $targetProjectDirectory = $targetProjectDirectory . DIRECTORY_SEPARATOR . $directory;

        $version = $this->ask($versionQuestion);

        // Alapértelmezett adatok
        $package = 'symfony/website-skeleton';
        // Itt jegyezzük be, ha vmi config-ot módosítani kell. Elérhető configok: `composer config --list`
        $composerConfigChanges = [];
        if ($version && version_compare($version, '4', '<')) {
            $package = 'symfony/framework-standard-edition';
            // SF3-ban 5.4 van megadva, ami nekünk nagyon nem jó, régi
            $composerConfigChanges = [
                'platform.php' => '7.1',
            ];
        }

        $output = [];
        $this->execCmd(sprintf('mkdir -p %s', $targetProjectDirectory));
        $this->execCmd(
            sprintf(
                'cd %s && composer create-project %s . %s',
                $targetProjectDirectory,
                $package,
                $version ? '"' . $version . '"' : ''
            ),
            $output
        );
        $this->output->writeln(implode("\n", $output));

        // Composer config upgrade, eg: platform.php --> 7.1
        if (count($composerConfigChanges) > 0) {
            $output = [];
            foreach ($composerConfigChanges as $key => $value) {
                $this->execCmd(sprintf('cd %s && composer config %s %s', $targetProjectDirectory, $key, $value), $output);
            }
            $this->execCmd(sprintf('cd %s && composer update', $targetProjectDirectory), $output);
            $this->output->writeln(implode("\n", $output));
        }

        $output = [];
        $this->execCmd(sprintf('cd %s && git init && git add . && git commit -m "Init"', $targetProjectDirectory), $output);
        $this->output->writeln(implode("\n", $output));

        return $targetProjectDirectory;
    }

    /**
     * EZT ITT NE HASZNÁLD!
     *
     * ComposerInstaller::COMPOSER_DEV => [... dev packages ...]
     * ComposerInstaller::COMPOSER_NODEV => [... nodev packages ...].
     *
     * Eg:
     * <code>
     *  return [ComposerInstaller::COMPOSER_DEV => ["friendsofphp/php-cs-fixer:~2.3.3"]];
     * </code>
     *
     * @return array
     */
    public function getRequireComposerPackages()
    {
        return [];
    }
}
