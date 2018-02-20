<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.10.11.
 * Time: 16:13
 */

namespace AppBundle\Wizard\Ez;

use AppBundle\Wizard\BaseSkeletonWizard;
use AppBundle\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class EzBuildMakefileWizard extends BaseSkeletonWizard implements PublicWizardInterface
{
    /**
     * A skeleton fájlok helye.
     *
     * @return string|array
     */
    protected function getSkeletonTemplateDirectory()
    {
        return 'EzBuilder';
    }

    /**
     * Itt kérjük be az adatokat a felhasználótól, ami alapján létrehozzuk a végső fájlokat.
     */
    protected function setVariables($targetProjectDirectory)
    {
        $config = [
            'ezsystems/ezplatform-ee' => 'studio-clean',
            'ezsystems/ezplatform-ee-demo' => 'demo',
            'ezsystems/ezplatform' => 'clean',
        ];
        $variables = [];

        $packageQuestion = new ChoiceQuestion(
            'Which package do you want to build? [<info>ezsystems/ezplatform-ee</info>]',
            array_keys($config),
            0
        );
        $variables['package'] = $this->ask($packageQuestion);
        $variables['ez_install_type'] = $config[$variables['package']];

        if ($variables['package'] != 'ezsystems/ezplatform') {
            $usernameQuestion = new Question('Username for <info>updates.ez.no</info> repository: ');
            $variables['auth_username'] = $this->ask($usernameQuestion);
            $passwordQuestion = new Question('Password for <info>updates.ez.no</info> repository: ');
            $variables['auth_password'] = $this->ask($passwordQuestion);
        }

        return $variables;
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
        $this->filesystem->dumpFile($targetPath, $fileContent);
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
        return 'makefile';
    }

    public function getName()
    {
        return 'eZ Project Builder makefile creator';
    }

    public function getInfo()
    {
        return 'Build a `makefile` which can init an eZ project.';
    }
}
