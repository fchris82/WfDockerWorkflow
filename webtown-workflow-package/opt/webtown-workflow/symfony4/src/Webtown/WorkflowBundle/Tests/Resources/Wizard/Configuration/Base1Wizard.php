<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.16.
 * Time: 12:58
 */

namespace App\Webtown\WorkflowBundle\Tests\Resources\Wizard\Configuration;

use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Wizards\BaseWizard;

class Base1Wizard extends BaseWizard
{
    public function __construct()
    {
        $this->ioManager = null;
        $this->commander = null;
        $this->eventDispatcher = null;
    }

    public function getDefaultName()
    {
        return 'Base 1 Wizard';
    }

    protected function build(BuildWizardEvent $event)
    {
        return null;
    }
}