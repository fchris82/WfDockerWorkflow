<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:24
 */

namespace Recipes\Php;

use App\Skeleton\DockerComposeSkeletonFile;
use Recipes\BaseRecipe;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class AbstractRecipe
 *
 * Symfony friendly environment
 *
 * @package Recipes\Symfony
 */
class Recipe extends BaseRecipe
{
    const NAME = 'php';
    const DEFAULT_VERSION = 'php7.2';

    public function getName()
    {
        return static::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>Php recipe</comment>')
            ->children()
                ->scalarNode('version')
                    ->info('<comment>Docker image tag. If you want to change image too, use the <info>image</info> option.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue(static::DEFAULT_VERSION)
                ->end()
                ->arrayNode('http_auth')
                    ->addDefaultsIfNotSet()
                    ->info('<comment>You can generate a user-password string here: http://www.htaccesstools.com/htpasswd-generator/ ( --> <info>htpasswd</info>).</comment>')
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('title')
                            ->defaultValue('Private zone')
                        ->end()
                        ->scalarNode('htpasswd')
                            ->cannotBeEmpty()
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->booleanNode('share_base_user_configs')
                    ->info('<comment>Here you can switch off or on to use user\'s .gitconfig, .ssh and .composer configs. Maybe you should switch off on CI.</comment>')
                    ->defaultTrue()
                ->end()
                ->arrayNode('server')
                    ->info('<comment>Server configuration</comment>')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')
                            ->info('<comment>Only for nginx. Here you can set the host. See: etc/vhost.conf file</comment>')
                            ->setDeprecated('We are using the `_` special settings so it may be redundant!')
                            ->example('project.docker.company.com')
                            ->cannotBeEmpty()
                            ->defaultValue('localhost')
                        ->end()
                        ->booleanNode('xdebug')
                            ->defaultFalse()
                            ->info('<comment>You can switch on and off the xdebug.</comment>')
                        ->end()
                        ->scalarNode('xdebug_ide_server_name')
                            ->cannotBeEmpty()
                            ->defaultValue('Docker')
                        ->end()
                        ->booleanNode('error_log')
                            ->defaultTrue()
                            ->info('<comment>You can switch on and off the PHP error log. (default is ON!)</comment>')
                        ->end()
                        ->booleanNode('nginx_debug')
                            ->defaultFalse()
                            ->info('<comment>You can switch on and off debug mode. IMPORTANT! The debug mode makes lot of logs!</comment>')
                        ->end()
                        ->scalarNode('max_post_size')
                            ->defaultValue('10M')
                            ->info('<comment>You can set the nginx <info>client_max_body_size</info> and php <info>max_post</info> and <info>max_file_upload</info>.</comment>')
                        ->end()
                        ->scalarNode('timeout')
                            ->defaultValue('30')
                            ->info('<comment>You can set the nginx <info>fastcgi_read_timeout</info> and php <info>max_execution_time</info>.</comment>')
                        ->end()
                        // @todo Ez ne innen jöjjön, hanem a wf config-ból
                        ->scalarNode('timezone')
                            ->defaultValue('Europe/Budapest')
                            ->info('<comment>You can set the server timezone.</comment>')
                        ->end()
                        ->scalarNode('locale')
                            ->defaultValue('hu_HU')
                            ->info('<comment>You can set the server locale.</comment>')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        // Volume settings
        if ($fileInfo->getFilename() == 'docker-compose.user-volumes.yml' && isset($config['share_base_user_configs']) && $config['share_base_user_configs'] > 0) {
            return new DockerComposeSkeletonFile($fileInfo);
        }

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
