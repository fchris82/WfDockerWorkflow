<?php

namespace App\Wizards\WfExtensionRecipe;

use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Exception\WizardSomethingIsRequiredException;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Wizards\WfExtension\Base\AbstractExtensionWizard;
use Symfony\Component\Console\Question\Question;

class WfExtensionRecipeWizard extends AbstractExtensionWizard
{
    const DIRECTORY_NAME = 'Recipes';

    public function getDefaultName()
    {
        return 'WF extensions: recipe init';
    }

    public function getDefaultGroup()
    {
        return 'WF dev';
    }

    public function getInfo()
    {
        return 'You can generate a new WF Recipe skeleton.';
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
        $recipeQuestion = new Question('Please give a class name. You have to finish with "Recipe", eg: <comment>MyCustomRecipe</comment>. You have to use unique name!');
        $recipeQuestion->setValidator(function ($answer) {
            $answer = trim($answer);
            if (!\is_string($answer) || 'Recipe' !== substr($answer, -6)) {
                throw new \RuntimeException('The class of recipe should be suffixed with \'Recipe\'');
            }
            if (strpos($answer, '_')) {
                throw new \RuntimeException('You need to use CamelCase syntax! Don\'t use `_` in the class name!');
            }
            if (!preg_match('/^[A-Z][a-zA-Z0-9]*Recipe$/', $answer)) {
                throw new \RuntimeException('Invalid class name! You need to start with upper letter, and you mustn\'t use special characters or space in the name!');
            }

            return $answer;
        });
        $recipeQuestion->setMaxAttempts(2);

        $nameQuestion = new Question('Name of wizard (configuration root name)');

        $io = $this->ioManager->getIo();
        $class = $io->askQuestion($recipeQuestion);
        $name = $io->askQuestion($nameQuestion);

        return [
            'recipe_class' => $class,
            'namespace' => substr($class, 0, -6),
            'name' => $name,
        ];
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $event)
    {
        parent::eventAfterBuildFile($event);
        /** @var SkeletonFile $skeletonFile */
        $skeletonFile = $event->getSkeletonFile();
        $skeletonFile
            ->move($this->getRelativeTargetDirectory($event->getSkeletonVar('namespace')));
        switch ($skeletonFile->getFileName()) {
            case 'Recipe.php':
                    $skeletonFile->rename($event->getSkeletonVar('recipe_class') . '.php');
        }
    }

    /**
     * @param BuildWizardEvent $event
     */
    protected function build(BuildWizardEvent $event)
    {
        // do nothing special
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
        $io->title(sprintf('The %s recipe created successfully', $event->getSkeletonVar('recipe_class')));
        if ($symlink) {
            $io->note(sprintf('The %s symlink was created!', $symlink));
        } else {
            $io->note('The symlink wasn\'t created!');
        }
        $io->listing([
            sprintf('Go to <comment>%s</comment> directory', $this->getRelativeTargetDirectory($event->getSkeletonVar('namespace'))),
            sprintf('Edit the <comment>%s.php</comment> file.', $event->getSkeletonVar('recipe_class')),
        ]);
    }

    protected function getRelativeTargetDirectory($namespace): string
    {
        return static::DIRECTORY_NAME . \DIRECTORY_SEPARATOR . $namespace;
    }
}
