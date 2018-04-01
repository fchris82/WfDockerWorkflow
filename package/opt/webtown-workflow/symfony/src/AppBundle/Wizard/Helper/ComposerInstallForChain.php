<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 12:32.
 */

namespace AppBundle\Wizard\Helper;

use AppBundle\Wizard\BaseWizard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerInstallForChain extends BaseWizard
{
    /**
     * @var string[]
     */
    protected $packages;

    /**
     * GitCloneWizardForChain constructor.
     *
     * @param $packages
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Command         $command
     */
    public function __construct($packages, InputInterface $input, OutputInterface $output, Command $command)
    {
        $this->packages = $packages;
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
        ComposerInstaller::installComposerPackages($targetProjectDirectory, $this->packages, $this->output);

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
}
