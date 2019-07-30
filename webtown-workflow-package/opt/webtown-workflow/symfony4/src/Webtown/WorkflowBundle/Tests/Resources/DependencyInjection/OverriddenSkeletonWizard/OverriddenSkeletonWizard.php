<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.18.
 * Time: 12:15
 */

namespace App\Webtown\WorkflowBundle\Tests\Resources\DependencyInjection\OverriddenSkeletonWizard;

use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;

class OverriddenSkeletonWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'Overridden skeleton wizard';
    }

    protected function build(BuildWizardEvent $event)
    {
    }
}
