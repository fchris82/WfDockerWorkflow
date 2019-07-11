<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 14:07
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Wizards;

use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;

class BaseSkeletonWizard extends \App\Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard
{
    /**
     * @var BuildWizardEvent
     */
    private $event;

    /**
     * @var bool
     */
    private $isHidden;

    /**
     * @var bool
     */
    private $builtCheckFile;

    /**
     * @var bool|\Exception
     */
    private $checkRequires;

    /**
     * @var callable
     */
    private $buildCall;

    /**
     * @var array
     */
    private $readVariables;

    public function getDefaultName()
    {
        return '';
    }

    protected function init(BuildWizardEvent $event)
    {
        parent::init($event);
        $this->registerCall($event, __METHOD__);
    }

    protected function build(BuildWizardEvent $event)
    {
        $this->registerCall($event, __METHOD__);
        if (\is_callable($this->buildCall)) {
            \call_user_func($this->buildCall, $event);
        }
    }

    protected function cleanUp(BuildWizardEvent $event)
    {
        parent::cleanUp($event);
        $this->registerCall($event, __METHOD__);
    }

    private function registerCall(BuildWizardEvent $event, $method)
    {
        $parameters = $event->getParameters();
        $parameters[$method] = true;
        $event->setParameters($parameters);
        $this->event = $event;
    }

    public function getBuildWizardEvent()
    {
        return $this->event;
    }

    /**
     * @param bool|\Exception $checkRequires
     *
     * @return $this
     */
    public function setCheckRequires($checkRequires)
    {
        $this->checkRequires = $checkRequires;

        return $this;
    }

    /**
     * @param bool $isHidden
     *
     * @return $this
     */
    public function setIsHidden(bool $isHidden)
    {
        $this->isHidden = $isHidden;

        return $this;
    }

    /**
     * @param string $builtCheckFile
     *
     * @return $this
     */
    public function setBuiltCheckFile(string $builtCheckFile)
    {
        $this->builtCheckFile = $builtCheckFile;

        return $this;
    }

    protected function getBuiltCheckFile()
    {
        return $this->builtCheckFile;
    }

    /**
     * @param callable $buildCall
     *
     * @return $this
     */
    public function setBuildCall(callable $buildCall)
    {
        $this->buildCall = $buildCall;

        return $this;
    }

    /**
     * @param array $readVariables
     *
     * @return $this
     */
    public function setReadVariables(array $readVariables)
    {
        $this->readVariables = $readVariables;

        return $this;
    }

    public function isHidden()
    {
        return null === $this->isHidden
            ? parent::isHidden()
            : $this->isHidden;
    }

    public function checkRequires($targetProjectDirectory)
    {
        if ($this->checkRequires instanceof \Exception) {
            throw $this->checkRequires;
        }

        return null === $this->checkRequires
            ? parent::checkRequires($targetProjectDirectory)
            : $this->checkRequires;
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        return null === $this->readVariables
            ? parent::readSkeletonVars($event)
            : $this->readVariables;
    }
}
