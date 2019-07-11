<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 10:29
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Wizards;

use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;

class BaseWizard extends \App\Webtown\WorkflowBundle\Wizards\BaseWizard
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
    private $isBuilt;

    /**
     * @var bool|\Exception
     */
    private $checkRequires;

    /**
     * @var callable
     */
    private $buildCall;

    public function getDefaultName()
    {
        return static::class;
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
     * @param bool $isBuilt
     *
     * @return $this
     */
    public function setIsBuilt(bool $isBuilt)
    {
        $this->isBuilt = $isBuilt;

        return $this;
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

    public function isHidden()
    {
        return null === $this->isHidden
            ? parent::isHidden()
            : $this->isHidden;
    }

    public function isBuilt($targetProjectDirectory)
    {
        return null === $this->isBuilt
            ? parent::isBuilt($targetProjectDirectory)
            : $this->isBuilt;
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
}
