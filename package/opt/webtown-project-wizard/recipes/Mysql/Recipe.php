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
        return self::NAME;
    }

    // @todo (Chris) Esetleg ki lehetne kapcsolni, hogy a merevlemezen akarjon menteni.
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
                    ->info('<comment>The <info>root</info> password</comment>')
                    ->isRequired()
                    ->cannotBeEmpty()
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
        if (isset($config['port']) && $config['port'] > 0 && $fileInfo->getFilename() == 'docker-compose.port.yml') {
            return new DockerComposeSkeletonFile($fileInfo);
        }

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
