<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.28.
 * Time: 21:33
 */

namespace AppBundle\Configuration;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Filesystem\Filesystem;

class Environment
{
    const CONFIG_PROGRAM_REPOSITORY = 'program_repository';
    const CONFIG_REVERSE_PROXY_PORT = 'reverse_proxy_port';
    const CONFIG_DEFAULT_LOCAL_TLD  = 'default_local_tld';
    const CONFIG_WORKING_DIRECTORY  = 'working_directory';
    const CONFIG_CONFIGURATION_FILE = 'configuration_file';

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * CACHE
     *
     * @var null|array
     */
    protected $config;

    /**
     * Environment constructor.
     *
     * @param string     $configFile
     * @param Filesystem $fileSystem
     */
    public function __construct($configFile, Filesystem $fileSystem)
    {
        $this->configFile = $configFile;
        $this->fileSystem = $fileSystem;
    }

    public function getConfigValue($name, $default = null)
    {
        $config = $this->readConfig();
        if (!array_key_exists($name, $config)) {
            return $default;
        }

        return $config[$name];
    }

    protected function readConfig()
    {
        if (is_null($this->config)) {
            if (!$this->fileSystem->exists($this->configFile) || !is_file($this->configFile)) {
                throw new InvalidConfigurationException(sprintf('Missing configuration file: `%s`', $this->configFile));
            }
            $this->config = [];
            if (preg_match_all('/^([^ #]+) += +(.*)/m', file_get_contents($this->configFile), $matches)) {
                foreach ($matches[1] as $n => $key) {
                    $this->config[$key] = $matches[2][$n];
                }
            }
        }

        return $this->config;
    }
}
