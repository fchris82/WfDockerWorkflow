<?php

namespace App\Recipes\{{ namespace }};

use App\Exception\SkipSkeletonFileException;
use App\Recipes\BaseRecipe;
use App\Skeleton\FileType\SkeletonFile;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Finder\SplFileInfo;

class {{ recipe_class }} extends BaseRecipe
{
    const NAME = '{{ name }}';

    public function getName()
    {
        return static::NAME;
    }

    /**
     * @see https://symfony.com/doc/current/components/config/definition.html
     *
     * @return ArrayNodeDefinition|\Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            // @todo
            ->info('<comment>???</comment>')
        ;

        return $rootNode;
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param $config
     *
     * @throws SkipSkeletonFileException
     *
     * @return SkeletonFile
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        // @todo

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
