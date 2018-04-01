<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 11:56.
 */

namespace AppBundle\Wizard\Symfony;

use AppBundle\Wizard\Base\GitCloneWizard;
use AppBundle\Wizard\Base\GitlabCISkeleton;
use AppBundle\Wizard\Base\PhpCsFixSkeleton;
use AppBundle\Wizard\Base\PhpMdSkeleton;
use AppBundle\Wizard\BaseChainWizard;
use AppBundle\Wizard\Docker\Slim;
use AppBundle\Wizard\Docker\Wide;
use AppBundle\Wizard\Helper\ExecuteComposerInstallForChain;
use AppBundle\Wizard\Helper\GitCommitWizardForChain;
use AppBundle\Wizard\Helper\SimpleCommandForChain;
use AppBundle\Wizard\PublicWizardInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class SymfonyProjectChainWizard.
 *
 * Felépít egy komplett Symfony projektet.
 */
class SymfonyProjectChainWizard extends BaseChainWizard implements PublicWizardInterface
{
    /**
     * @var string
     */
    protected $dockerWizard;

    public function build($targetProjectDirectory)
    {
        $directoryQuestion = new Question('Select target directory: ', '.');
        $directory = $this->ask($directoryQuestion);
        $targetProjectDirectory = $targetProjectDirectory . DIRECTORY_SEPARATOR . $directory;
        $dockerWizardQuestion = new ChoiceQuestion('Select docker mode', [Wide::class, Slim::class], 0);
        $this->dockerWizard = $this->ask($dockerWizardQuestion);

        // A Symfony Builder tud más könyvtárba telepíteni, azonban már itt rákérdezünk a könyvtárakra, tehát ezt itt kikapcsoljuk.
        $this->container->get(SymfonyBuildWizard::class)->setAskDirectory(false);

        $targetProjectDirectory = parent::build($targetProjectDirectory);

        // Visszakapcsoljuk a SymfonyBuildWizard-ot
        $this->container->get(SymfonyBuildWizard::class)->setAskDirectory(true);

        $this->output->writeln('<info>Edit the uncommitted files!</info>');

        return $targetProjectDirectory;
    }

    protected function getWizardNames()
    {
        return [
            GitCloneWizard::class,
            new SimpleCommandForChain('composer install'),
            new SimpleCommandForChain('git init && git add . && git commit -m "Init"'),
            PhpMdSkeleton::class,
            PhpCsFixSkeleton::class,
            new ExecuteComposerInstallForChain($this, $this->input, $this->output, $this->command),
            new GitCommitWizardForChain('Add PHPMD and PHP-CS-FIXER', $this->input, $this->output, $this->command),
            $this->dockerWizard,
            GitlabCISkeleton::class,
        ];
    }

    public function getName()
    {
        return 'Full Symfony project with Docker';
    }

    public function getInfo()
    {
        return 'Build a Symfony project with Docker, PHPMD, PHP-CS fixer and gitlab CI';
    }
}
