<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:35
 */

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

class DumpEvent extends Event
{
    /**
     * @var string
     */
    protected $targetPath;

    /**
     * @var string
     */
    protected $contents;

    /**
     * DumpEvent constructor.
     * @param string $targetPath
     * @param string $contents
     */
    public function __construct($targetPath, $contents)
    {
        $this->targetPath = $targetPath;
        $this->contents = $contents;
    }

    /**
     * @return string
     */
    public function getTargetPath()
    {
        return $this->targetPath;
    }

    /**
     * @param string $targetPath
     *
     * @return DumpEvent
     */
    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     *
     * @return DumpEvent
     */
    public function setContents($contents)
    {
        $this->contents = $contents;

        return $this;
    }
}
