<?php

namespace Recipes\GitlabCiWebtownRunner;

use App\Skeleton\DockerComposeSkeletonFile;
use Recipes\GitlabCi\Recipe as BaseRecipe;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Finder\SplFileInfo;

class Recipe extends BaseRecipe
{
    const NAME = 'gitlab_ci_webtown_runner';

    public function getName()
    {
        return static::NAME;
    }

    public static function getSkeletonParents()
    {
        return [BaseRecipe::class];
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        /**
         *  gitlab_ci_webtown_runner:
         *      share_home_with: engine
         *      volumes:
         *          mysql:
         *              data: /var/lib/mysql
         */

        $rootNode
            ->info('<comment>GitLab CI Webtown Runner</comment>')
            ->children()
                ->arrayNode('share_home_with')
                    ->info('<comment>Share composer cache or other things between tests. Service name list.</comment>')
                    ->scalarPrototype()->end()
                    ->defaultValue(['engine'])
                ->end()
                ->arrayNode('volumes')
                    ->info('<comment>Register template volumes.</comment>')
                    ->useAttributeAsKey('service')
                    ->variablePrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return ['data' => $v];
                            })
                            ->end()
                        ->end()
                        ->validate()
                            ->always(function ($v) {
                                foreach ($v as $name => $target) {
                                    if (!is_string($name)) {
                                        throw new InvalidConfigurationException(sprintf('You have to use string key in `%s`.volumes', static::NAME));
                                    }
                                }

                                return $v;
                            })
                        ->end()
                        ->example([
                            'service1' => '/usr/mysql/data',
                            'service2' => ['data' => '/usr/mysql/data', 'config' => '/usr/mysql/config'],
                        ])
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    public function getTemplateVars($projectPath, $recipeConfig, $globalConfig)
    {
        $baseVars = parent::getTemplateVars($projectPath, $recipeConfig, $globalConfig);

        $output = [];
        exec(sprintf('cd %s && git rev-parse --short HEAD', $projectPath), $output);
        return array_merge($baseVars, [
            'git_hash' => trim(implode('', $output)),
        ]);
    }

    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        // share_home_with settings
        if ($fileInfo->getFilename() == 'docker-compose.home.yml' && isset($config['share_home_with']) && count($config['share_home_with']) > 0) {
            return new DockerComposeSkeletonFile($fileInfo);
        }
        // volumes settings
        if ($fileInfo->getFilename() == 'docker-compose.volumes.yml' && isset($config['volumes']) && count($config['volumes']) > 0) {
            return new DockerComposeSkeletonFile($fileInfo);
        }

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
