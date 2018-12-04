<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 15:52
 */

namespace App\Extension\Installer;

use Symfony\Component\Filesystem\Filesystem;

class LocalInstaller implements InstallerInterface
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * LocalInstaller constructor.
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'local';
    }

    /**
     * @inheritdoc
     */
    public function install(string $source, string $target)
    {
        $this->fileSystem->mirror($source, $target, null, [
            'override' => true,
            'delete' => true,
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function getPriority(): int
    {
        return 0;
    }
}
