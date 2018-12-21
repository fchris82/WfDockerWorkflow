<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 17:20
 */

namespace App\Webtown\WorkflowBundle\Skeleton;


class SkeletonHelper
{
    const DIR = 'skeletons';

    public static function generateTwigNamespace(\ReflectionClass $class)
    {
        $className = $class->getName();

        return str_replace('\\', '', $className);
    }
}
