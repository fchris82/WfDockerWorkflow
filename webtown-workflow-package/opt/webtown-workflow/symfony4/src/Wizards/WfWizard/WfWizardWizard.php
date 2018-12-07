<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.22.
 * Time: 12:59
 */

namespace App\Wizards\WfWizard;

use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Wizards\BaseSkeletonWizard;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class WfWizardWizard extends BaseSkeletonWizard
{
    const RELATIVE_TARGET_DIRECTORY = 'webtown-workflow-package/opt/webtown-workflow/symfony4/src/Wizards';

    /**
     * @var array
     */
    protected $variables;

    public function getDefaultName()
    {
        return 'WF wizard init';
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
        if (!file_exists($targetProjectDirectory . \DIRECTORY_SEPARATOR . self::RELATIVE_TARGET_DIRECTORY)) {
            throw new WizardSomethingIsRequiredException('You can use this command in the `webtown-workflow` develop directory.');
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        $wizardQuestion = new Question('Please give a class name. You have to finish with "Wizard", eg: <comment>MyCustomWizard</comment>');
        $wizardQuestion->setValidator(function ($answer) {
            $answer = trim($answer);
            if (!\is_string($answer) || 'Wizard' !== substr($answer, -6)) {
                throw new \RuntimeException('The class of wizard should be suffixed with \'Wizard\'');
            }
            if (strpos($answer, '_')) {
                throw new \RuntimeException('You need to use CamelCase syntax! Don\'t use `_` in the class name!');
            }
            if (!preg_match('/^[a-zA-Z][a-zA-Z0-9]*Wizard$/', $answer)) {
                throw new \RuntimeException('Invalid class name! You need to start with letter, and you mustn\'t use special characters or space in the name!');
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

        $this->variables = [
            'wizard_class' => $class,
            'namespace' => substr($class, 0, -6),
            'parent_wizard' => $useSkeletons ? 'BaseSkeletonWizard' : 'BaseWizard',
            'name' => $name,
            'group' => $group,
        ];

        return $this->variables;
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event)
    {
        parent::eventAfterBuildFile($event);
        $skeletonFile = $event->getSkeletonFile();
        switch ($skeletonFile->getRelativePathname()) {
            case 'Wizard.php':
                $skeletonFile
                    ->setRelativePath($this->getRelativeTargetDirectory())
                    ->setFileName($this->variables['wizard_class'] . '.php');
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
        if ('BaseSkeletonWizard' == $this->variables['parent_wizard']) {
            $target = $event->getWorkingDirectory() . \DIRECTORY_SEPARATOR
                . $this->getRelativeTargetDirectory() . \DIRECTORY_SEPARATOR
                . 'skeletons';
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
        $io = $this->ioManager->getIo();
        $io->newLine(2);
        $io->title(sprintf('The %s wizard created succesfully', $this->variables['wizard_class']));
        $io->listing([
            sprintf('Go to <comment>%s</comment> directory', $this->getRelativeTargetDirectory()),
            sprintf('Edit the <comment>%s.php</comment> file.', $this->variables['wizard_class']),
            'Create git repository: <comment>git init && git add . && git commit -m "Init"</comment> to "save" and share your wizard.',
        ]);
    }

    protected function getRelativeTargetDirectory()
    {
        return static::RELATIVE_TARGET_DIRECTORY
            . \DIRECTORY_SEPARATOR
            . $this->variables['namespace'];
    }
}