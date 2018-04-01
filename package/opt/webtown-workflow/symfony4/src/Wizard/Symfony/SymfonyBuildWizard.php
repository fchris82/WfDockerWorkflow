<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.07.
 * Time: 12:30.
 */

namespace App\Wizard\Symfony;

use App\Wizard\BaseWizard;
use App\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class SymfonyBuildWizard.
 *
 * Egy Symfony projektet hoz létre a `symfony` parancs segítségével.
 */
class SymfonyBuildWizard extends BaseWizard implements PublicWizardInterface
{
    protected $askDirectory = true;

    public function getName()
    {
        return 'Symfony';
    }

    public function getInfo()
    {
        return 'Create a symfony project';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }

    public function build($targetProjectDirectory)
    {
        $directoryQuestion = new Question('Add meg a könyvtárat, ahová szeretnéd telepíteni: ', '.');
        $versionQuestion = new Question('Add meg verziót [Üresen hagyva a legutóbbi stabil verziót szedi le, egyébként: <info>lts</info>, <info>demo</info>, <info>x.x</info>] ');

        $directory = $this->askDirectory
            ? $this->ask($directoryQuestion)
            : '.';
        $targetProjectDirectory = $targetProjectDirectory . DIRECTORY_SEPARATOR . $directory;

        $version = $this->ask($versionQuestion);
        $command = '/usr/local/bin/symfony';
        $output = [];
        $this->execCmd(sprintf('mkdir -p %s', $targetProjectDirectory));
        if ($version == 'demo') {
            $this->execCmd(sprintf(
                'cd %s && %s %s sf_demo && mv ./sf_demo/* . && mv ./sf_demo/.[!.]* . && rm -rf ./sf_demo',
                $targetProjectDirectory,
                $command,
                'demo'
            ), $output);
        } else {
            $this->execCmd(sprintf(
                'cd %s && %s %s . %s',
                $targetProjectDirectory,
                $command,
                'new',
                $version
            ), $output);
        }
        $this->output->writeln(implode("\n", $output));

        $output = [];
        $this->execCmd(sprintf('cd %s && git init && git add . && git commit -m "Init"', $targetProjectDirectory), $output);

        return $targetProjectDirectory;
    }

    public function getRequireComposerPackages()
    {
        return [];
    }

    public function setAskDirectory($askDirectory)
    {
        $this->askDirectory = $askDirectory;
    }
}
