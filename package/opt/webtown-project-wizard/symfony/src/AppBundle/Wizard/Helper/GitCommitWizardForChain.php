<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 12:18.
 */

namespace AppBundle\Wizard\Helper;

use AppBundle\Exception\GitUncommittedChangesException;
use AppBundle\Wizard\BaseWizard;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GitCommitWizardForChain extends BaseWizard
{
    /**
     * Commit message.
     *
     * @var string
     */
    protected $commit;

    /**
     * GitCloneWizardForChain constructor.
     *
     * @param string          $commit
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Command         $command
     */
    public function __construct($commit, InputInterface $input, OutputInterface $output, Command $command)
    {
        $this->commit = $commit;
        $this->input = $input;
        $this->output = $output;
        $this->command = $command;
    }

    public function isBuilt($targetProjectDirectory)
    {
        $helper = new CheckGitUncommittedChangesForChain($this->input, $this->output, $this->command);
        try {
            $helper->build($targetProjectDirectory);
        } catch (GitUncommittedChangesException $e) {
            return false;
        }

        return true;
    }

    public function build($targetProjectDirectory)
    {
        // @todo (Chris) A commit üzenetet maszkolni kellene
        $this->execCmd(sprintf('cd %s && git add . && git commit -m "%s"', $targetProjectDirectory, $this->commit));

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
