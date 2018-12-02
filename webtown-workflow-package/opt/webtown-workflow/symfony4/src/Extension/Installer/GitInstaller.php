<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 15:58
 */

namespace App\Extension\Installer;


use App\Environment\Commander;
use Symfony\Component\Filesystem\Filesystem;

class GitInstaller implements InstallerInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var Commander
     */
    protected $commander;

    /**
     * ComposerInstaller constructor.
     * @param Filesystem $fileSystem
     * @param Commander $commander
     */
    public function __construct(Filesystem $fileSystem, Commander $commander)
    {
        $this->fileSystem = $fileSystem;
        $this->commander = $commander;
    }

    public function getName()
    {
        return 'git';
    }

    public function install($source, $target)
    {
        if ($this->fileSystem->exists($target)) {
            $this->run(sprintf('rm -rf %s', $target));
        }
        $this->fileSystem->mkdir($target);
        $cmd = sprintf('git clone %s .', $source);
        $this->commander->run($cmd, $target);
    }
}
