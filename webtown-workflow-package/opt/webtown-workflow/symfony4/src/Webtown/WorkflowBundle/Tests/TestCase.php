<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.05.29.
 * Time: 10:53
 */

namespace App\Webtown\WorkflowBundle\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Class TestCase
 *
 * Handles protected methods.
 */
class TestCase extends BaseTestCase
{
    protected function executeProtectedMethod($object, $method, $args)
    {
        return $this->getMethod(\get_class($object), $method)->invokeArgs($object, $args);
    }

    protected function getProtectedProperty($object, $propertyName)
    {
        $class = new \ReflectionClass(\get_class($object));
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * @param string $class
     * @param string $methodName
     *
     * @throws \ReflectionException
     *
     * @return \ReflectionMethod
     */
    protected function getMethod($class, $methodName)
    {
        $class = new \ReflectionClass($class);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }
}
