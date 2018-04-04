<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 19:22
 */

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Filesystem\Filesystem;

class FinishEvent extends Event
{
    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * FinishEvent constructor.
     * @param Filesystem $fileSystem
     */
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return Filesystem
     */
    public function getFileSystem()
    {
        return $this->fileSystem;
    }
}
