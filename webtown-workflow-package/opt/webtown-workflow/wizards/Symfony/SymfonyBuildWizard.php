<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.07.
 * Time: 12:30.
 */

namespace Wizards\Symfony;

use Wizards\BaseWizard;
use App\Wizard\WizardInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class SymfonyBuildWizard.
 *
 * Egy Symfony projektet hoz létre a `symfony` parancs segítségével.
 */
class SymfonyBuildWizard extends BaseWizard implements WizardInterface
{
    protected $askDirectory = true;

    public function getDefaultName()
    {
        return 'Symfony';
    }

    public function getInfo()
    {
        return 'Create a symfony project';
    }

    public function getDefaultGroup()
    {
        return 'Builder';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return $this->wfIsInitialized($targetProjectDirectory);
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
        $this->run(sprintf('mkdir -p %s', $targetProjectDirectory));
        if ($version == 'demo') {
            $this->run(sprintf(
                'cd %s && %s %s sf_demo && mv ./sf_demo/* . && mv ./sf_demo/.[!.]* . && rm -rf ./sf_demo',
                $targetProjectDirectory,
                $command,
                'demo'
            ));
        } else {
            $this->run(sprintf(
                'cd %s && %s %s . %s',
                $targetProjectDirectory,
                $command,
                'new',
                $version
            ));
        }

        $this->run(sprintf('cd %s && git init && git add . && git commit -m "Init"', $targetProjectDirectory));

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
