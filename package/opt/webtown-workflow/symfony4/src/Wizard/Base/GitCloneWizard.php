<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.11.
 * Time: 16:30.
 */

namespace App\Wizard\Base;

use App\Wizard\BaseGitCloneWizard;
use App\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

class GitCloneWizard extends BaseGitCloneWizard implements PublicWizardInterface
{
    protected $default;

    /**
     * GitCloneWizard constructor.
     *
     * @param $default
     */
    public function __construct(Filesystem $filesystem, $default)
    {
        $this->filesystem = $filesystem;
        $this->default = $default;
    }

    protected function getRepository($targetProjectDirectory)
    {
        $question = new Question(
            sprintf('Set a git repository [<info>%s</info>]:', $this->default),
            $this->default
        );
        $repository = $this->ask($question);

        return $repository;
    }

    /**
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

    public function getName()
    {
        return 'Git clone init';
    }

    public function getInfo()
    {
        return 'Make a git clone';
    }
}
