<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.10.11.
 * Time: 16:13
 */

namespace Wizards\Ez;

use Wizards\BaseWizard;
use App\Wizard\WizardInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class EzBuildWizard extends BaseWizard implements WizardInterface
{
    protected $askDirectory = true;

    public function build($targetProjectDirectory)
    {
        $directoryQuestion = new Question('Installation directory: ', '.');

        $directory = $this->askDirectory
            ? $this->ask($directoryQuestion)
            : '.';
        $targetProjectDirectory = $targetProjectDirectory . DIRECTORY_SEPARATOR . $directory;
        $config = [
            'ezsystems/ezplatform-ee' => 'studio-clean',
            'ezsystems/ezplatform-ee-demo' => 'demo',
            'ezsystems/ezplatform' => 'clean',
            'ezsystems/ezplatform-demo' => 'platform-demo',
        ];

        $packageQuestion = new ChoiceQuestion(
            'Which package do you want to build? [<info>ezsystems/ezplatform-ee</info>]',
            array_keys($config),
            0
        );
        $package = $this->ask($packageQuestion);
        $ez_install_type = $config[$package];

        $composerRequired = [];
        $requireKaliopMigrationQuestion = new ConfirmationQuestion('Do you want to install <info>kaliop/ezmigrationbundle</info>?', true, '/^[yi]/i');
        $requireKaliopMigration = $this->ask($requireKaliopMigrationQuestion);
        if ($requireKaliopMigration) {
            $composerRequired[] = 'kaliop/ezmigrationbundle';
        }
        $requireDoctrineOrmQuestion = new ConfirmationQuestion('Do you need <info>doctrine migration</info>?', true, '/^[yi]/i');
        $requireDoctrineMigrations = $this->ask($requireDoctrineOrmQuestion);
        if ($requireDoctrineMigrations) {
            $composerRequired[] = 'doctrine/doctrine-migrations-bundle';
        }

        $this->run(sprintf('mkdir -p %s', $targetProjectDirectory));
        $this->execCmdInDocker(sprintf('composer create-project %s .', $package), $targetProjectDirectory);

        $this->run(sprintf('cd %s && git init && git add . && git commit -m "Init"', $targetProjectDirectory));

        if ($package != 'ezsystems/ezplatform') {
            $this->createAuthJson($targetProjectDirectory);
        }

        if (count($composerRequired) > 0) {
            $this->runComposerRequire($targetProjectDirectory, $composerRequired);
            $this->run(sprintf('cd %s && git init && git add . && git commit -m "Add some composer package"', $targetProjectDirectory));
        }

        if ($requireKaliopMigration) {
            $this->output->writeln('<info>Please register the <comment>kaliop migration bundle</comment> in the <comment>AppKernel.php</comment> file</info>');
        }
        if ($requireDoctrineMigrations) {
            $this->output->writeln('<info>Please register the <comment>doctrine migration bundle</comment> in the <comment>AppKernel.php</comment> file</info>');
        }

        return $targetProjectDirectory;
    }

    protected function createAuthJson($targetProjectDirectory)
    {
        $this->output->writeln('');
        $usernameQuestion = new Question('<comment>Username</comment> for <info>updates.ez.no</info> repository: ');
        $auth_username = $this->ask($usernameQuestion);
        $passwordQuestion = new Question('<comment>Password</comment>: ');
        $auth_password = $this->ask($passwordQuestion);

        $tpl = <<<EOL
{
    "http-basic": {
        "updates.ez.no": {
            "username": "$auth_username",
            "password": "$auth_password"
         }
    }
}
EOL;

        file_put_contents($targetProjectDirectory . '/auth.json', $tpl);
        $this->output->writeln('The <info>auth.json</info> is created');
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

    public function getDefaultName()
    {
        return 'eZ Project Builder';
    }

    public function getInfo()
    {
        return 'Create an eZ project';
    }

    public function getDefaultGroup()
    {
        return 'Builder';
    }

    public function isBuilt($targetProjectDirectory)
    {
        return $this->wfIsInitialized($targetProjectDirectory);
    }

    protected function getDockerImage()
    {
        return 'fchris82/symfony:ez2';
    }
}
