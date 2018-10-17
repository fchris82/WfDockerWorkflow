<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:24
 */

namespace Recipes\Mysql;

use Recipes\BaseRecipe;
use App\Skeleton\DockerComposeSkeletonFile;
use Symfony\Component\Finder\SplFileInfo;

class Recipe extends BaseRecipe
{
    const NAME = 'mysql';

    public function getName()
    {
        return static::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>Include a MySQL service</comment>')
            ->children()
                ->arrayNode('defaults')
                    ->info('<comment>You can set some defaults for all containers!</comment>')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('version')
                            ->info('<comment>Docker image tag</comment>')
                        ->end()
                        ->scalarNode('password')
                            ->info('<comment>The <info>root</info> password.</comment>')
                        ->end()
                        ->booleanNode('local_volume')
                            ->info('<comment>You can switch the using local volume.</comment>')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('databases')
                    ->info('<comment>Configuration of the MySql containers.</comment>')
                    ->useAttributeAsKey('docker_container_name')
                    ->prototype('array')
                        ->info('<comment>The docker container name. You have to link through this! Eg: <info>mysql -u root -p -h [docker_container_name]</info>.</comment>')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('version')
                                ->info('<comment>Docker image tag (you can define default for all, see <info>mysql.defaults.version</info>)</comment>')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('database')
                                ->info('<comment>Database name</comment>')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('password')
                                ->info('<comment>The <info>root</info> password. (you can define default for all, see <info>mysql.defaults.password</info>)</comment>')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->booleanNode('local_volume')
                                ->info('<comment>You can switch the using local volume.</comment>')
                                ->defaultTrue()
                            ->end()
                            ->integerNode('port')
                                ->info('<comment>If you want to enable this container from outside set the port number.</comment>')
                                ->defaultValue(0)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->beforeNormalization()
                ->always(function($v) {
                    if (is_array($v)) {
                        // Backward compatibility
                        if (!array_key_exists('databases', $v)) {
                            $w['databases']['mysql'] = $v;
                            $v = $w;
                        // Handle defaults
                        } elseif (array_key_exists('defaults', $v) && is_array($v['defaults'])) {
                            foreach ($v['defaults'] as $key => $defaultValue) {
                                foreach ($v['databases'] as $dockerContainerName => $config) {
                                    // If the config empty, then we use only defaults
                                    if (!$config) {
                                        $config = [
                                            'database' => $dockerContainerName . '_db',
                                        ];
                                    } elseif (!is_array($config)) {
                                        throw new \InvalidArgumentException(sprintf(
                                            'Invalid configuration value in the <info>mysql.databases.%s</info> place. You have to use array or null instead of %s',
                                            $dockerContainerName,
                                            gettype($config)
                                        ));
                                    }
                                    if (!array_key_exists($key, $config) || (!$config[$key] && $config[$key] !== false)) {
                                        $v['databases'][$dockerContainerName][$key] = $defaultValue;
                                    }
                                }
                            }
                        }
                    }

                    return $v;
                })
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        // Port settings
        if ($fileInfo->getFilename() == 'docker-compose.port.yml' && isset($config['port']) && $config['port'] > 0) {
            return new DockerComposeSkeletonFile($fileInfo);
        }
        // Volume settings
        if ($fileInfo->getFilename() == 'docker-compose.volume.yml' && isset($config['local_volume']) && $config['local_volume']) {
            return new DockerComposeSkeletonFile($fileInfo);
        }

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
