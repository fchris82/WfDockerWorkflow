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

class ComposerInstaller implements InstallerInterface
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

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'composer';
    }

    /**
     * @inheritdoc
     */
    public function install(string $source, string $target)
    {
        if ($this->fileSystem->exists($target)) {
            $this->run(sprintf('rm -rf %s', $target));
        }
        $this->fileSystem->mkdir($target);
        $cmd = sprintf('composer create-project %s .', $source);
        $this->commander->run($cmd, $target);
    }

    /**
     * @inheritdoc
     */
    public static function getPriority(): int
    {
        return 50;
    }
}
