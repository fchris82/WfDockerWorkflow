<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:06
 */

namespace App\Webtown\WorkflowBundle\Tests\Resources\DependencyInjection\SimpleWizard;

use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Wizards\BaseWizard;

class SimpleWizard extends BaseWizard
{
    public function getDefaultName()
    {
        return 'Simple wizard';
    }

    protected function build(BuildWizardEvent $event)
    {
    }
}
