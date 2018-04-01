<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 13:28
 */

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

class BuildInitEvent extends Event
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
    protected $targetDirectory;

    /**
     * @var string
     */
    protected $configHash;

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * BuildInitEvent constructor.
     *
     * @param array $config
     * @param string $projectPath
     * @param string $targetDirectory
     * @param string $configHash
     */
    public function __construct(array $config, $projectPath, $targetDirectory, $configHash)
    {
        $this->config = $config;
        $this->projectPath = $projectPath;
        $this->targetDirectory = $targetDirectory;
        $this->configHash = $configHash;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @return string
     */
    public function getProjectPath()
    {
        return $this->projectPath;
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

    /**
     * @return string
     */
    public function getConfigHash()
    {
        return $this->configHash;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return array_merge(
            [
                '%wf.project_path%'     => $this->projectPath,
                '%wf.target_directory%' => $this->targetDirectory,
                '%wf.config_hash%'      => $this->configHash,
            ],
            $this->parameters
        );
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }
}
