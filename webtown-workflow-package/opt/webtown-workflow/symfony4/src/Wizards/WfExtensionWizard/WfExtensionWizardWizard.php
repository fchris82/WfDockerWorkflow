<?php

namespace App\Wizards\WfExtensionWizard;

use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Skeleton\FileType\SkeletonFile;
use App\Wizards\WfExtension\Base\AbstractExtensionWizard;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class WfExtensionWizardWizard extends AbstractExtensionWizard
{
    const DIRECTORY_NAME = 'Wizards';

    public function getDefaultName()
    {
        return 'WF extensions: wizard init';
    }

    public function getDefaultGroup()
    {
        return 'WF dev';
    }

    public function getInfo()
    {
        return 'You can generate a new WF Wizard skeleton.';
    }

    /**
     * @param string $targetProjectDirectory
     *
     * @throws WizardSomethingIsRequiredException
     *
     * @return bool
     */
    public function checkRequires($targetProjectDirectory)
    {
        // `wf-extension` keyword exists in the composer.json file
        if (!$this->workingDirectoryIsAnExtension($targetProjectDirectory)) {
            throw new WizardSomethingIsRequiredException('You can use this command in an extension directory!');
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        $wizardQuestion = new Question('Please give a class name. You have to finish with "Wizard", eg: <comment>MyCustomWizard</comment>. You have to use unique name!');
        $wizardQuestion->setValidator(function ($answer) {
            $answer = trim($answer);
            if (!\is_string($answer) || 'Wizard' !== substr($answer, -6)) {
                throw new \RuntimeException('The class of wizard should be suffixed with \'Wizard\'');
            }
            if (strpos($answer, '_')) {
                throw new \RuntimeException('You need to use CamelCase syntax! Don\'t use `_` in the class name!');
            }
            if (!preg_match('/^[A-Z][a-zA-Z0-9]*Wizard$/', $answer)) {
                throw new \RuntimeException('Invalid class name! You need to start with upper letter, and you mustn\'t use special characters or space in the name!');
            }

            return $answer;
        });
        $wizardQuestion->setMaxAttempts(2);

        $baseQuestion = new ConfirmationQuestion('Do you want to use skeleton files?');
        $nameQuestion = new Question('"Human" name of wizard (it will be shown in table)');
        $groupQuestion = new Question('Group');

        $io = $this->ioManager->getIo();
        $class = $io->askQuestion($wizardQuestion);
        $useSkeletons = $io->askQuestion($baseQuestion);
        $name = $io->askQuestion($nameQuestion);
        $group = $io->askQuestion($groupQuestion);

        return [
            'wizard_class' => $class,
            'namespace' => substr($class, 0, -6),
            'parent_wizard' => $useSkeletons ? 'BaseSkeletonWizard' : 'BaseWizard',
            'name' => $name,
            'group' => $group,
        ];
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event)
    {
        parent::eventAfterBuildFile($event);
        /** @var SkeletonFile $skeletonFile */
        $skeletonFile = $event->getSkeletonFile();
        $skeletonFile
            ->move($this->getRelativeTargetDirectory($event->getSkeletonVar('namespace')));
        switch ($skeletonFile->getRelativePathname()) {
            case 'Wizard.php':
                    $skeletonFile->rename($event->getSkeletonVar('wizard_class') . '.php');
        }
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @todo (Chris) refactorálni
     */
    protected function build(BuildWizardEvent $event)
    {
        // Create skeletons directory
        if ('BaseSkeletonWizard' == $event->getSkeletonVar('parent_wizard')) {
            $target = implode(\DIRECTORY_SEPARATOR,[
                $event->getWorkingDirectory(),
                static::DIRECTORY_NAME,
                $event->getSkeletonVar('namespace'),
                'skeletons'
            ]);
            $this->fileSystem->mkdir($target);
            $this->ioManager->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> directory has been created.</info>',
                \dirname($target),
                basename($target)
            ));
        }
    }

    protected function cleanUp(BuildWizardEvent $event)
    {
        parent::cleanUp($event);

        $symlink = $this->createSymlink(
            $event->getWorkingDirectory(),
            static::DIRECTORY_NAME,
            $event->getSkeletonVar('namespace')
        );

        $io = $this->ioManager->getIo();
        $io->newLine(2);
        $io->title(sprintf('The %s wizard created successfully', $event->getSkeletonVar('wizard_class')));
        if ($symlink) {
            $io->note(sprintf('The %s symlink was created!', $symlink));
        } else {
            $io->note('The symlink wasn\'t created!');
        }
        $io->listing([
            sprintf('Go to <comment>%s</comment> directory', $this->getRelativeTargetDirectory($event->getSkeletonVar('namespace'))),
            sprintf('Edit the <comment>%s.php</comment> file.', $event->getSkeletonVar('wizard_class')),
        ]);
    }

    protected function getRelativeTargetDirectory($namespace): string
    {
        return static::DIRECTORY_NAME . \DIRECTORY_SEPARATOR . $namespace;
    }
}
