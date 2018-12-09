<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.22.
 * Time: 12:59
 */

namespace App\Wizards\WfExtension;

use App\Environment\Commander;
use App\Environment\IoManager;
use App\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Wizards\BaseSkeletonWizard;
use App\Wizards\WfExtension\Base\AbstractExtensionWizard;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class WfExtensionWizard extends AbstractExtensionWizard
{

    public function getDefaultName()
    {
        return 'WF extension init';
    }

    public function getDefaultGroup()
    {
        return 'WF dev';
    }

    public function getInfo()
    {
        return 'You can generate a new WF Extension base to your `~/.webtown-workflow/extensions` directory.';
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
        if (!$this->workingDirectoryIsWfDevOrHostConfiguration($targetProjectDirectory)) {
            throw new WizardSomethingIsRequiredException(
                'You can use this command in the `webtown-workflow` develop directory OR in your configuration directory: ' . $this->hostConfigurationPath
            );
        }

        return parent::checkRequires($targetProjectDirectory);
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        $io = $this->ioManager->getIo();
        $composerNameQuestion = new Question('You have to set a composer package name ( https://getcomposer.org/doc/04-schema.md#name )');
        $composerNameQuestion->setValidator(function ($answer) {
            $answer = strtolower(trim($answer));
            if (!$answer) {
                throw new \RuntimeException('Please set a directory name! You can change it later in the generated composer.json file.');
            }
            if (!preg_match('|[a-z0-9][a-z0-9\-\_]*/[a-z0-9\-\_]+|', $answer)) {
                throw new \RuntimeException('The name is invalid. You have to use like this: `company/extension-name`');
            }

            return $answer;
        });
        $composerNameQuestion->setMaxAttempts(2);

        $composerName = $io->askQuestion($composerNameQuestion);
        $defaultDirectoryName = strpos('/', $composerName) !== false
            ? basename($composerName)
            : null;

        $directoryNameQuestion = new Question('Please give a directory name', $defaultDirectoryName);
        $directoryNameQuestion->setValidator(function ($answer) {
            $answer = trim($answer);
            if (!$answer) {
                throw new \RuntimeException('Please set a directory name!');
            }
            $target = implode(\DIRECTORY_SEPARATOR, [
                $this->hostConfigurationPath,
                static::EXTENSION_DIRECTORY_NAME,
                $answer
            ]);
            if ($this->fileSystem->exists($target) && is_dir($target)) {
                throw new \RuntimeException(sprintf(
                    'The `%s` directory exists at `%s` path! Please use a name that doesn\'t exist yet.',
                    $answer,
                    $target
                ));
            }

            return $answer;
        });
        $directoryNameQuestion->setMaxAttempts(2);

        $directoryName = $io->askQuestion($directoryNameQuestion);

        $authorNameQuestion = new Question('Your name (composer.json, author section)');
        $authorName = $io->askQuestion($authorNameQuestion);
        $authorEmailQuestion = new Question('Your e-mail address');
        $authorEmail = $io->askQuestion($authorEmailQuestion);

        return [
            'composer_name' => $composerName,
            'directory_name' => $directoryName,
            'target_directory' =>  implode(\DIRECTORY_SEPARATOR, [
                $this->hostConfigurationPath,
                static::EXTENSION_DIRECTORY_NAME,
                $directoryName,
            ]),
            'author_name' => $authorName,
            'author_email' => $authorEmail,
        ];
    }

    protected function build(BuildWizardEvent $event)
    {
        // Do everything elsewhere
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent)
    {
        parent::eventAfterBuildFile($postBuildSkeletonFileEvent);

        $postBuildSkeletonFileEvent->getSkeletonFile()->move(
            $postBuildSkeletonFileEvent->getSkeletonVar('target_directory')
        );
    }

    protected function cleanUp(BuildWizardEvent $event)
    {
        parent::cleanUp($event);

        $io = $this->ioManager->getIo();
        $io->newLine(2);
        $io->title('The extension created successfully');
        $io->listing([
            sprintf('Go to <comment>%s</comment> directory', $event->getSkeletonVar('target_directory')),
            'Edit the <comment>composer.json</comment> if you want.',
            'You can create new <comment>Recipes</comment> or <comment>Wizards</comment>. Call <info>wizard</info> in that directory.',
            'Create git repository: <comment>git init && git add . && git commit -m "Init"</comment> to "save" and share your extension.',
        ]);
    }
}
