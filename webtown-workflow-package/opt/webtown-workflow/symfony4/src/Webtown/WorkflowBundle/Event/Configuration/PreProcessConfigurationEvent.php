<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 13:28
 */

namespace App\Webtown\WorkflowBundle\Event\Configuration;

use Symfony\Component\EventDispatcher\Event;

class PreProcessConfigurationEvent extends Event
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $projectPath;

    /**
     * @var string
     */
    protected $wfVersion;

    /**
     * PreProcessConfigurationEvent constructor.
     *
     * @param array  $config
     * @param string $projectPath
     * @param string $wfVersion
     */
    public function __construct(array $config, string $projectPath, string $wfVersion)
    {
        $this->config = $config;
        $this->projectPath = $projectPath;
        $this->wfVersion = $wfVersion;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getProjectPath(): string
    {
        return $this->projectPath;
    }

    /**
     * @param string $projectPath
     *
     * @return $this
     */
    public function setProjectPath(string $projectPath)
    {
        $this->projectPath = $projectPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getWfVersion(): string
    {
        return $this->wfVersion;
    }
}
