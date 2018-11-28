<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 11:30
 */

namespace Wizards\PhpMd;

use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use Wizards\BaseSkeletonWizard;

class PhpMdWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'PhpMd install';
    }

    public function getInfo()
    {
        return 'Add PhpMd to the project.';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    public function getBuiltCheckFile()
    {
        return 'phpmd.xml';
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
        if (!file_exists($targetProjectDirectory . '/composer.json')) {
            throw new WizardSomethingIsRequiredException(sprintf('Initialized composer is required for this!'));
        }
        if (!$this->wfIsInitialized($targetProjectDirectory)) {
            throw new WizardWfIsRequiredException($this, $targetProjectDirectory);
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @return string
     */
    public function build(BuildWizardEvent $event)
    {
        $this->runCmdInContainer('composer require --dev phpmd/phpmd', $event->getWorkingDirectory());
    }
}
