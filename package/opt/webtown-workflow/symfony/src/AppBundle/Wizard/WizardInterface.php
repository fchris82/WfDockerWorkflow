<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace AppBundle\Wizard;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface WizardInterface
{
    public function isBuilt($targetProjectDirectory);

    /**
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory);

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
    public function getRequireComposerPackages();

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
