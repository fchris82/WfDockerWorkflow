<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 12:52
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Simple;

use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class SimpleRecipe extends BaseRecipe
{
    /**
     * Parent class names
     *
     * @var array|string[]
     */
    protected static $skeletonParents = [];

    public function getName()
    {
        return 'simple';
    }

    /**
     * @return array|string[]
     */
    public static function getSkeletonParents()
    {
        return array_merge(parent::getSkeletonParents(), self::$skeletonParents);
    }

    /**
     * @param array|string[] $skeletonParents
     */
    public static function setSkeletonParents($skeletonParents)
    {
        self::$skeletonParents = $skeletonParents;
    }

    public function makefileFormat(string $pattern, array $items)
    {
        return $this->makefileMultilineFormatter($pattern, $items);
    }
}
