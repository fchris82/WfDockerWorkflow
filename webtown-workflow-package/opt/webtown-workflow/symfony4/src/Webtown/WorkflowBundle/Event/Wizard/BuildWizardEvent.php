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
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getWorkingDirectory(): string
    {
        return $this->workingDirectory;
    }

    /**
     * @param string $workingDirectory
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setWorkingDirectory(string $workingDirectory)
    {
        $this->workingDirectory = $workingDirectory;

        return $this;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple setter
     */
    public function getSkeletonVars(): array
    {
        return $this->skeletonVars;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function addSkeletonVar($key, $value)
    {
        $this->skeletonVars[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed|null
     *
     * @codeCoverageIgnore Simple getter
     */
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
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setSkeletonVars(array $skeletonVars)
    {
        $this->skeletonVars = $skeletonVars;

        return $this;
    }

    /**
     * @return array
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     *
     * @codeCoverageIgnore Simple setter
     */
    public function addParameter($key, $value)
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    /**
     * @param      $key
     * @param null $defaultValue
     *
     * @return mixed|null
     *
     * @codeCoverageIgnore Simple getter
     */
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
     *
     * @codeCoverageIgnore Simple setter
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}
