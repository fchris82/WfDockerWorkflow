<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 12:32.
 */

namespace App\Wizard\Helper;

use Wizards\BaseChainWizard;
use Wizards\BaseWizard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExecuteComposerInstallForChain extends BaseWizard
{
    /**
     * @var BaseChainWizard
     */
    protected $chainWizard;

    /**
     * GitCloneWizardForChain constructor.
     *
     * @param BaseChainWizard $chainWizard
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Command         $command
     */
    public function __construct(BaseChainWizard $chainWizard, InputInterface $input, OutputInterface $output, Command $command)
    {
        $this->chainWizard = $chainWizard;
        $this->input = $input;
        $this->output = $output;
        $this->command = $command;
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }

    public function build($targetProjectDirectory)
    {
        $this->chainWizard->installComposerPackages($targetProjectDirectory);

        return $targetProjectDirectory;
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
        // TODO: Implement getName() method.
    }
}
