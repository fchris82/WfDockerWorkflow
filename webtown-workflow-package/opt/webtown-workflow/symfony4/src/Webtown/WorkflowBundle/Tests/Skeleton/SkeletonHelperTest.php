<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 14:08
 */

namespace App\Webtown\WorkflowBundle\Tests\Skeleton;

use App\Webtown\WorkflowBundle\Skeleton\SkeletonHelper;
use PHPUnit\Framework\TestCase;

class SkeletonHelperTest extends TestCase
{
    /**
     * @param $class
     * @param $result
     *
     * @throws \ReflectionException
     *
     * @dataProvider getNamespaces
     */
    public function testGenerateTwigNamespace($class, $result)
    {
        $reflectionClass = new \ReflectionClass($class);

        $response = SkeletonHelper::generateTwigNamespace($reflectionClass);
        $this->assertEquals($result, $response);
    }

    public function getNamespaces()
    {
        return [
            [\Exception::class, 'Exception'],
            [SkeletonHelper::class, 'AppWebtownWorkflowBundleSkeletonSkeletonHelper'],
        ];
    }
}