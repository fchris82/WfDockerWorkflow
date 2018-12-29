<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.13.
 * Time: 21:18
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;
use Symfony\Component\Finder\Finder;

class Filesystem extends BaseFilesystem
{
    /**
     * @var null|string
     */
    protected $initDirectory;

    /**
     * @var null|string
     */
    protected $alias;

    /**
     * @var array
     */
    protected $contents = [];

    public function __construct($initDirectory, $alias = null)
    {
        $this->initDirectory = $initDirectory;
        $this->alias = $alias;

        $finder = new Finder();
        $finder->files()->ignoreDotFiles(false)->in($initDirectory);
        foreach ($finder as $fileInfo) {
            $path = $this->aliasMask($fileInfo->getPathname());
            $content = file_get_contents($fileInfo->getPathname());
            $this->contents[$path] = $content;
        }
    }

    protected function aliasMask($path)
    {
        if (null !== $this->alias) {
            return str_replace($this->initDirectory, $this->alias, $path);
        }

        return $path;
    }

    public function getContents()
    {
        return $this->contents;
    }

    public function exists($files)
    {
        $files = \is_array($files) ? $files : [$files];
        $hits = [];
        foreach ($files as $file) {
            $file = $this->aliasMask($file);
            foreach ($this->contents as $path => $content) {
                if ($path == $file || 0 === strpos($path, $file . \DIRECTORY_SEPARATOR)) {
                    $hits[] = $file;
                    continue 2;
                }
            }
        }

        return \count($files) == \count($hits);
    }

    public function dumpFile($filename, $content)
    {
        $this->contents[$filename] = $content;
    }

    public function appendToFile($filename, $content)
    {
        $base = isset($this->contents[$filename]) ? $this->contents[$filename] : '';
        $this->contents[$filename] = $base . $content;
    }

    public function mkdir($dirs, $mode = 0777)
    {
        // do nothing
        return;
    }

    public function touch($files, $time = null, $atime = null)
    {
        $files = \is_array($files) ? $files : [$files];
        foreach ($files as $file) {
            $targetFile = $this->aliasMask($file);
            if (!$this->exists($targetFile)) {
                $this->dumpFile($targetFile, '');
            }
        }
    }

    public function copy($origin, $target, $overwriteNewerFiles = false)
    {
        $origin = $this->aliasMask($origin);
        $target = $this->aliasMask($target);

        $copied = 0;
        foreach ($this->contents as $path => $content) {
            if ($origin == $path || 0 === strpos($path, $origin . \DIRECTORY_SEPARATOR)) {
                ++$copied;
                $newPath = str_replace($origin, $target, $path);
                if (!$this->exists($newPath) || $overwriteNewerFiles) {
                    $this->contents[$newPath] = $content;
                }
            }
        }

        if (0 == $copied) {
            throw new FileNotFoundException();
        }
    }

    public function rename($origin, $target, $overwrite = false)
    {
        $origin = $this->aliasMask($origin);
        $target = $this->aliasMask($target);

        $renamed = 0;
        $newContents = [];
        foreach ($this->contents as $path => $content) {
            if ($origin == $path || 0 === strpos($path, $origin . \DIRECTORY_SEPARATOR)) {
                ++$renamed;
                $newPath = str_replace($origin, $target, $path);
                if (!$this->exists($newPath) || $overwrite) {
                    $newContents[$newPath] = $content;
                }
            } else {
                if (!array_key_exists($path, $newContents)) {
                    $newContents[$path] = $content;
                }
            }
        }

        if (0 == $renamed) {
            throw new FileNotFoundException();
        }

        $this->contents = $newContents;
    }

    public function remove($files)
    {
        $files = \is_array($files) ? $files : [$files];
        foreach ($files as $file) {
            $file = $this->aliasMask($file);
            foreach ($this->contents as $path => $content) {
                if ($path == $file || 0 === strpos($path, $file . \DIRECTORY_SEPARATOR)) {
                    unset($this->contents[$path]);
                    continue 2;
                }
            }
        }
    }

    public function chmod($files, $mode, $umask = 0000, $recursive = false)
    {
        // @todo (Chris)
    }
}
