<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.21.
 * Time: 17:20
 */

namespace App\Webtown\WorkflowBundle\Skeleton;

class SkeletonHelper
{
    const SKELETONS_DIR = 'skeletons';
    const TEMPLATES_DIR = 'template';

    public static function generateTwigNamespace(\ReflectionClass $class): string
    {
        $className = $class->getName();

        return str_replace('\\', '', $className);
    }
}
