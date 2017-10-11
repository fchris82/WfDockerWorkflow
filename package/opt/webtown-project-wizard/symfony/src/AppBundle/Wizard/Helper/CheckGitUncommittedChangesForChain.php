<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 13:37.
 */

namespace AppBundle\Wizard\Helper;

use AppBundle\Exception\GitUncommittedChangesException;
use AppBundle\Wizard\BaseWizard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CheckGitUncommittedChangesForChain extends BaseWizard
{
    /**
     * GitCloneWizardForChain constructor.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Command         $command
     */
    public function __construct(InputInterface $input, OutputInterface $output, Command $command)
    {
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
        $this->execCmd(sprintf('cd %s && if  git diff-index --quiet HEAD --', $targetProjectDirectory), [], function ($return, $output) {
            if ($return == 1) {
                throw new GitUncommittedChangesException('There are some uncommmitted changes!');
            } elseif ($return != 0) {
                throw new \Exception('Something went wrong! Git exists?');
            }
        });

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
    public function getComposerPackages()
    {
        return [];
    }
}
