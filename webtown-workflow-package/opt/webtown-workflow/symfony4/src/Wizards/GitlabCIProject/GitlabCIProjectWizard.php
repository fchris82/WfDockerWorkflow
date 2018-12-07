<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.12.
 * Time: 15:45
 */

namespace App\Wizards\GitlabCIProject;

use App\Environment\Commander;
use App\Environment\EnvParser;
use App\Environment\EzEnvironmentParser;
use App\Environment\IoManager;
use App\Environment\WfEnvironmentParser;
use App\Event\Wizard\BuildWizardEvent;
use App\Exception\WizardSomethingIsRequiredException;
use App\Exception\WizardWfIsRequiredException;
use App\Wizards\BaseSkeletonWizard;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

class GitlabCIProjectWizard extends BaseSkeletonWizard
{
    /**
     * @var EzEnvironmentParser
     */
    protected $ezEnvironmentParser;

    /**
     * @var WfEnvironmentParser
     */
    protected $wfEnvironmentParser;

    /**
     * @var EnvParser
     */
    protected $envParser;

    /**
     * GitlabCIProjectWizard constructor.
     *
     * @param EnvParser                $envParser
     * @param EventDispatcherInterface $eventDispatcher
     * @param \Twig_Environment        $twig
     * @param Filesystem               $filesystem
     */
    public function __construct(
        EzEnvironmentParser $ezEnvironmentParser,
        WfEnvironmentParser $wfEnvironmentParser,
        EnvParser $envParser,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->ezEnvironmentParser = $ezEnvironmentParser;
        $this->wfEnvironmentParser = $wfEnvironmentParser;
        $this->envParser = $envParser;
    }

    public function getDefaultName()
    {
        return 'GitlabCI';
    }

    public function getInfo()
    {
        return 'Initialize projet to Gitlab CI';
    }

    public function getDefaultGroup()
    {
        return 'Composer';
    }

    protected function getSkeletonVars(BuildWizardEvent $event)
    {
        $targetProjectDirectory = $event->getWorkingDirectory();
        $variables = $this->ezEnvironmentParser->getSymfonyEnvironmentVariables($targetProjectDirectory);
        $wfConfiguration = $this->wfEnvironmentParser->getWorkflowConfiguration($targetProjectDirectory);
        $symfonyRecipeName = null;
        foreach ($wfConfiguration['recipes'] as $recipeName => $recipeConfig) {
            if (0 === strpos($recipeName, 'symfony')) {
                $symfonyRecipeName = $recipeName;
                break;
            }
        }

        return array_merge($variables, [
            'project_name' => basename($this->envParser->get('ORIGINAL_PWD', $targetProjectDirectory)),
            'sf_recipe_name' => $symfonyRecipeName,
        ]);
    }

    protected function getBuiltCheckFile()
    {
        return '.gitlab-ci.yml';
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
        if (!$this->wfEnvironmentParser->wfIsInitialized($targetProjectDirectory)) {
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
        $workingDirectory = $event->getWorkingDirectory();
        // Ha létezik parameters.yml, akkor annak is létrehozunk egy gitlab verziót
        if ($this->fileSystem->exists($workingDirectory . '/app/config/parameters.yml.dist')) {
            $this->fileSystem->copy(
                $workingDirectory . '/app/config/parameters.yml.dist',
                $workingDirectory . '/app/config/parameters.gitlab-ci.yml'
            );
            $this->ioManager->writeln(sprintf(
                '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been created or modified.</info>',
                'app/config',
                'parameters.gitlab-ci.yml'
            ));
        }
    }
}