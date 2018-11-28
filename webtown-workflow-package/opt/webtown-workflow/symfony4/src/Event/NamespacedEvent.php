<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 10:44
 */

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

class NamespacedEvent extends Event
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * NamespacedEvent constructor.
     *
     * @param $namespace
     */
    public function __construct($namespace)
    {
        if (\is_object($namespace)) {
            $namespace = \get_class($namespace);
        }
        $this->namespace = $namespace;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string|object $namespace
     *
     * @return bool
     */
    public function isNamespace($namespace)
    {
        if (\is_object($namespace)) {
            $namespace = \get_class($namespace);
        }

        return $this->namespace == $namespace;
    }
}
