<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:06
 */

namespace App\Webtown\WorkflowBundle\Tests\Resources\DependencyInjection\SimpleSkeletonWizard;


use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;

class SimpleSkeletonWizard extends BaseSkeletonWizard
{

    public function getDefaultName()
    {
        return 'Simple skeleton wizard';
    }

    protected function build(BuildWizardEvent $event)
    {
    }
}
