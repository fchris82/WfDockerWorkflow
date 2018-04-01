<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:24
 */

namespace Recipes\Mysql;

use AppBundle\Configuration\BaseRecipe;
use AppBundle\Skeleton\DockerComposeSkeletonFile;
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
                ->scalarNode('version')
                    ->info('<comment>Docker image tag</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('database')
                    ->info('<comment>Database name</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('password')
                    ->info('<comment>The <info>root</info> password.</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->booleanNode('local_volume')
                    ->info('<comment>You can switch the using local volume.</comment>')
                    ->defaultTrue()
                ->end()
                ->integerNode('port')
                    ->info('<comment>If you want to enable this container from outside set the port number.</comment>')
                    ->defaultNull()
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
