<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.15.
 * Time: 11:51
 */

namespace App\Wizards\WfSymfonyEnvironment;

use App\Webtown\WorkflowBundle\Environment\Commander;
use App\Webtown\WorkflowBundle\Environment\EnvParser;
use App\Webtown\WorkflowBundle\Environment\EzEnvironmentParser;
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Wizards\BaseSkeletonWizard;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class DockerProject.
 *
 * Lapos könyvtárstruktúrát húz rá a projektre.
 *
 * <code>
 *  DockerProject
 *  ├── [...]
 *  │
 *  └── .wf.yml.dist
 * </code>
 */
class WfEnvironmentWizardProjectWizard extends BaseSkeletonWizard
{
    /**
     * @var EzEnvironmentParser
     */
    protected $ezEnvironmentParser;

    /**
     * @var EnvParser
     */
    protected $envParser;

    public function __construct(
        EzEnvironmentParser $EzEnvironmentParser,
        EnvParser $envParser,
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        parent::__construct($ioManager, $commander, $eventDispatcher, $twig, $filesystem);
        $this->ezEnvironmentParser = $EzEnvironmentParser;
        $this->envParser = $envParser;
    }

    public function getDefaultName()
    {
        return 'WF Symfony Environment';
    }

    public function getInfo()
    {
        return 'Create a Symfony WF environment for the existing project.';
    }

    public function getDefaultGroup()
    {
        return 'WF';
    }

    protected function build(BuildWizardEvent $event)
    {
        // TODO: Implement build() method.
    }

    /**
     * Az itt visszaadott fájllal ellenőrizzük, hogy az adott dekorátor lefutott-e már.
     * <code>
     *  protected function getBuiltCheckFile() {
     *      return '.docker';
     *  }
     * </code>.
     *
     * @return string
     */
    protected function getBuiltCheckFile()
    {
        return '.wf.yml.dist';
    }

    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        $targetProjectDirectory = $event->getWorkingDirectory();
        $variables = $this->ezEnvironmentParser->getSymfonyEnvironmentVariables($targetProjectDirectory);

        $phpVersionQuestion = new Question('Which PHP version do you want to use? [<info>7.2</info>]', '7.2');
        $variables['php_version'] = $this->ask($phpVersionQuestion);

        $variables['project_name'] = basename($this->envParser->get('ORIGINAL_PWD', $targetProjectDirectory));
        $variables['current_wf_version'] = $this->ioManager->getInput()->getOption('wf-version');

        return $variables;
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
        parent::eventBeforeDumpTargetExists($event);

        switch (strtolower($event->getSkeletonFile()->getBaseFileInfo()->getFilename())) {
            case '.gitignore':
            case 'readme.md':
                $event->getSkeletonFile()->setHandleExisting(SkeletonFile::HANDLE_EXISTING_APPEND);
                break;
        }
    }
}
