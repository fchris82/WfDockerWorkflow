<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 18:26
 */

namespace App\Webtown\WorkflowBundle\Event\Extension;

use App\Webtown\WorkflowBundle\Extension\Installer\InstallerInterface;
use Symfony\Component\EventDispatcher\Event;

class InstallEvent extends Event
{
    /**
     * @var string
     */
    protected $fullSource;

    /**
     * @var InstallerInterface
     */
    protected $installer;

    /**
     * @var string
     */
    protected $targetPath;

    /**
     * InstallEvent constructor.
     *
     * @param string             $fullSource
     * @param InstallerInterface $installer
     * @param string             $targetPath
     */
    public function __construct(string $fullSource, InstallerInterface $installer, string $targetPath)
    {
        $this->fullSource = $fullSource;
        $this->installer = $installer;
        $this->targetPath = $targetPath;
    }

    /**
     * @return string
     */
    public function getFullSource(): string
    {
        return $this->fullSource;
    }

    /**
     * @param string $fullSource
     *
     * @return $this
     */
    public function setFullSource(string $fullSource)
    {
        $this->fullSource = $fullSource;

        return $this;
    }

    /**
     * @return InstallerInterface
     */
    public function getInstaller(): InstallerInterface
    {
        return $this->installer;
    }

    /**
     * @param InstallerInterface $installer
     *
     * @return $this
     */
    public function setInstaller(InstallerInterface $installer)
    {
        $this->installer = $installer;

        return $this;
    }

    /**
     * @return string
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }

    /**
     * @param string $targetPath
     *
     * @return $this
     */
    public function setTargetPath(string $targetPath)
    {
        $this->targetPath = $targetPath;

        return $this;
    }
}
