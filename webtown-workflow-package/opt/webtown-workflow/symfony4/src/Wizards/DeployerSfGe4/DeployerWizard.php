<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:55
 */

namespace App\Wizards\DeployerSfGe4;

use App\Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use App\Webtown\WorkflowBundle\Exception\WizardWfIsRequiredException;
use App\Wizards\DeployerSfLe3\DeployerWizard as BaseDeployerWizard;

class DeployerWizard extends BaseDeployerWizard
{
    public function getDefaultName()
    {
        return 'Deployer (SF >= 4)';
    }

    public function getInfo()
    {
        return 'Add Deployer for a Symfony project (SF >= 4)';
    }

    /**
     * @param $targetProjectDirectory
     *
     * @throws WizardSomethingIsRequiredException
     * @throws WizardWfIsRequiredException
     *
     * @return bool
     */
    public function checkRequires($targetProjectDirectory)
    {
        parent::checkRequires($targetProjectDirectory);

        $this->checkSfVersion($targetProjectDirectory, 4, '>=');

        return true;
    }
}
