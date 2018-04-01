<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.06.
 * Time: 16:47.
 */

namespace App\Wizard\Docker\Wide;

use App\Wizard\BaseWizard;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class MoveProjectFiles extends BaseWizard
{
    const TARGET_DIRECTORY = 'project';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * MoveFiles constructor.
     *
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
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

    public function isBuilt($targetProjectDirectory)
    {
        $base = rtrim($targetProjectDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return $this->filesystem->exists($base . self::TARGET_DIRECTORY)
            && !$this->filesystem->exists($base . 'app');
    }

    public function build($targetProjectDirectory)
    {
        $tmpDirectoryName = '.' . md5(date('YmdHis'));
        $tmpTargetPath = $targetProjectDirectory . DIRECTORY_SEPARATOR . $tmpDirectoryName;
        // Create the project directory
        $this->filesystem->mkdir($tmpTargetPath);
        $finder = new Finder();
        $finder
            ->in($targetProjectDirectory)
            ->depth(0)
            ->ignoreDotFiles(false)
            ->notName($tmpDirectoryName)
            ->notName('.git')
            ->notName('.idea')
            ->notName('.gitlab-ci.yml');
        /** @var SplFileInfo $projectItem */
        foreach ($finder as $projectItem) {
            $this->filesystem->rename($projectItem->getPathname(), $tmpTargetPath . DIRECTORY_SEPARATOR . $projectItem->getFilename());
        }

        $this->filesystem->rename($tmpTargetPath, $targetProjectDirectory . DIRECTORY_SEPARATOR . self::TARGET_DIRECTORY);

        return $targetProjectDirectory;
    }
}
