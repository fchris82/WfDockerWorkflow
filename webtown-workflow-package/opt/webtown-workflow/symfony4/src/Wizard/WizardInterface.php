<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace App\Wizard;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface WizardInterface
{
    public function getDefaultName();

    public function getDefaultGroup();

    public function getInfo();

    public function isHidden();

    public function isBuilt($targetProjectDirectory);

    /**
     * @return string
     */
    public function runBuild($targetProjectDirectory);

    /**
     * @param InputInterface $input
     *
     * @return WizardInterface
     */
    public function setInput(InputInterface $input);

    /**
     * @param OutputInterface $output
     *
     * @return WizardInterface
     */
    public function setOutput(OutputInterface $output);

    /**
     * @param Command $command
     *
     * @return WizardInterface
     */
    public function setCommand(Command $command);
}
