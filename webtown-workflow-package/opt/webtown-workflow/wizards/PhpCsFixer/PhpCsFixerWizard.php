<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:35
 */

namespace Wizards\PhpCsFixer;

use App\Event\SkeletonBuild\DumpFileEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Skeleton\FileType\SkeletonFile;
use Wizards\BaseSkeletonWizard;

class PhpCsFixerWizard extends BaseSkeletonWizard
{
    public function getDefaultName()
    {
        return 'PhpCsFixer install';
    }

    public function getInfo()
    {
        return 'Add PhpCsFixer to the project.';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    /**
     * @param $targetProjectDirectory
     *
     * @return bool
     *
     * @throws WizardSomethingIsRequiredException
     * @throws WizardWfIsRequiredException
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

    protected function getBuiltCheckFile()
    {
        return '.php_cs.dist';
    }

    /**
     * @param $targetProjectDirectory
     *
     * @return string
     */
    public function build($targetProjectDirectory)
    {
        $this->runCmdInContainer('composer require --dev friendsofphp/php-cs-fixer', $targetProjectDirectory);

        return $targetProjectDirectory;
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
        parent::eventBeforeDumpTargetExists($event);

        switch ($event->getSkeletonFile()->getBaseFileInfo()->getFilename()) {
            case '.gitignore':
                $event->getSkeletonFile()->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
        }
    }
}
