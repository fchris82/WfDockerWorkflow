<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.28.
 * Time: 15:53
 */

namespace App\Webtown\WorkflowBundle\Event\Wizard;

use Symfony\Component\EventDispatcher\Event;

class BuildWizardEvent extends Event
{
    /**
     * @var string
     */
    protected $workingDirectory;

    /**
     * @var array
     */
    protected $skeletonVars = [];

    /**
     * @var array
     */
    protected $parameters = [];

    /**
     * BuildWizardEvent constructor.
     *
     * @param string $workingDirectory
     */
    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @return string
     */
    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @param string $workingDirectory
     *
     * @return $this
     */
    public function setWorkingDirectory(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }

    /**
     * @return array
     */
    public function getSkeletonVars(): array
    {
        return $this->skeletonVars;
    }

    public function addSkeletonVar($key, $value)
    {
        $this->skeletonVars[$key] = $value;

        return $this;
    }

    public function getSkeletonVar(string $key, $default = null)
    {
        if (!array_key_exists($key, $this->skeletonVars)) {
            return $default;
        }

        return $this->skeletonVars[$key];
    }

    /**
     * @param array $skeletonVars
     *
     * @return $this
     */
    public function setSkeletonVars(array $skeletonVars)
    {
        $this->skeletonVars = $skeletonVars;

        return $this;
    }

    /**
     * @return array
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function addParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function getParameter($key, $defaultValue = null)
    {
        if (array_key_exists($key, $this->parameters)) {
            return $this->parameters[$key];
        }

        return $defaultValue;
    }

    /**
     * @param array $parameters
     *
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
