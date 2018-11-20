<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.13.
 * Time: 14:56
 */

namespace App\Wizard;


class ConfigurationItem
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $enabled;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var integer
     */
    protected $priority;

    /**
     * ConfigurationItem constructor.
     * @param string|object $class
     * @param bool $enabled
     * @param string $group
     * @param int $priority
     */
    public function __construct($class, string $name, bool $enabled = true, string $group = "", int $priority = 0)
    {
        $this->class = is_object($class) ? get_class($class) : $class;
        $this->name = $name;
        $this->enabled = $enabled;
        $this->group = $group;
        $this->priority = $priority;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param bool $enabled
     *
     * @return $this
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @param string $group
     *
     * @return $this
     */
    public function setGroup(string $group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }
}
