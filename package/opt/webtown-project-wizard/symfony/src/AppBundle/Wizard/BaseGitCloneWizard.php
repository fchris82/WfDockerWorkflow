<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 17:41.
 */

namespace AppBundle\Wizard;

use AppBundle\Exception\ProjectHasDecoratedException;
use Symfony\Component\Filesystem\Filesystem;

abstract class BaseGitCloneWizard extends BaseWizard
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    abstract protected function getRepository($targetProjectDirectory);

    /**
     * Ellenőrzi, hogy a prjekt már az alábbival dekorálva lett-e már.
     *
     * @param $targetProjectDirectory
     *
     * @return bool
     */
    public function isBuilt($targetProjectDirectory)
    {
        $testDirectory = rtrim($targetProjectDirectory, DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . '.git';

        return $this->filesystem->exists($testDirectory);
    }

    public function build($targetProjectDirectory)
    {
        if ($this->isBuilt($targetProjectDirectory)) {
            throw new ProjectHasDecoratedException();
        }

        $repository = $this->getRepository($targetProjectDirectory);

        $this->output->writeln("\n <comment>⏲</comment> <info>Start clone from <comment>$repository</comment>...</info>\n");

        $this->gitClone($repository, $targetProjectDirectory);

        $this->execCmd(sprintf('rm -rf %s/.git', $targetProjectDirectory));

        return $targetProjectDirectory;
    }

    protected function gitClone($repository, $targetProjectDirectory)
    {
        $output = $this->execCmd(sprintf('git clone --depth=1 %s %s', $repository, $targetProjectDirectory));

        return $output;
    }
}
