<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.12.
 * Time: 10:23.
 */

namespace AppBundle\Wizard\Helper;

use AppBundle\Wizard\BaseWizard;

class SimpleCommandForChain extends BaseWizard
{
    /**
     * @var string
     */
    protected $cmd;

    /**
     * SimpleCommandForChain constructor.
     *
     * @param string $cmd
     */
    public function __construct($cmd)
    {
        $this->cmd = $cmd;
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }

    /**
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory)
    {
        $this->execCmd(sprintf('cd %s && %s', $targetProjectDirectory, $this->cmd));

        return $targetProjectDirectory;
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
    public function getComposerPackages()
    {
        return [];
    }
}
