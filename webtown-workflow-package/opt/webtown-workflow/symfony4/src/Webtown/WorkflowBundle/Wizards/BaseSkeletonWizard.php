<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace App\Webtown\WorkflowBundle\Wizards;

use App\Webtown\WorkflowBundle\DependencyInjection\Compiler\TwigExtendingPass;
use App\Webtown\WorkflowBundle\Environment\Commander;
use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\DumpFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PostBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFileEvent;
use App\Webtown\WorkflowBundle\Event\SkeletonBuild\PreBuildSkeletonFilesEvent;
use App\Webtown\WorkflowBundle\Event\Wizard\BuildWizardEvent;
use App\Webtown\WorkflowBundle\Exception\WizardHasAlreadyBuiltException;
use App\Webtown\WorkflowBundle\Skeleton\BuilderTrait;
use App\Webtown\WorkflowBundle\Skeleton\FileType\SkeletonFile;
use App\Webtown\WorkflowBundle\Skeleton\SkeletonManagerTrait;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class BaseSkeleton.
 *
 * Wizard, that has skeleton files that decorates an existing project with or create a new.
 */
abstract class BaseSkeletonWizard extends BaseWizard
{
    use SkeletonManagerTrait;
    use BuilderTrait;

    /**
     * @var array
     */
    protected $workflowConfigurationCache;

    /**
     * BaseSkeleton constructor.
     *
     * @param IoManager                $ioManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param \Twig_Environment        $twig
     * @param Filesystem               $filesystem
     */
    public function __construct(
        IoManager $ioManager,
        Commander $commander,
        EventDispatcherInterface $eventDispatcher,
        \Twig_Environment $twig,
        Filesystem $filesystem
    ) {
        $this->twig = $twig;
        $this->fileSystem = $filesystem;
        parent::__construct($ioManager, $commander, $eventDispatcher);
    }

    /**
     * Here you can ask data and variables from user or set them.
     */
    protected function readSkeletonVars(BuildWizardEvent $event)
    {
        return [];
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws WizardHasAlreadyBuiltException
     * @throws \Exception
     */
    public function initBuild(BuildWizardEvent $event)
    {
        parent::initBuild($event);

        $event->setSkeletonVars($this->readSkeletonVars($event));

        $this->printHeader($event);
        $this->doBuildFiles($event);
    }

    /**
     * @param BuildWizardEvent $event
     *
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    protected function doBuildFiles(BuildWizardEvent $event)
    {
        $skeletonFiles = $this->buildSkeletonFiles($event->getSkeletonVars());
        $this->dumpSkeletonFiles($skeletonFiles);
    }

    protected function printHeader(BuildWizardEvent $event)
    {
        $output = $this->ioManager->getOutput();
        $output->writeln("\n <comment>⏲</comment> <info>Start build...</info>\n");

        $table = new Table($output);
        $table
            ->setHeaders(['Placeholder', 'Value']);
        foreach ($event->getSkeletonVars() as $key => $value) {
            $table->addRow([
                $key,
                \is_array($value) || \is_object($value)
                    ? json_encode($value, JSON_PRETTY_PRINT)
                    : $value,
            ]);
        }
        $table->render();
    }

    public function isBuilt($targetProjectDirectory)
    {
        if ($this->getBuiltCheckFile()) {
            return $this->fileSystem->exists($targetProjectDirectory . '/' . $this->getBuiltCheckFile());
        }

        return false;
    }

    protected function getBuiltCheckFile()
    {
        return null;
    }

    protected function eventBeforeBuildFiles(PreBuildSkeletonFilesEvent $event)
    {
    }

    protected function eventBeforeBuildFile(PreBuildSkeletonFileEvent $preBuildSkeletonFileEvent)
    {
    }

    protected function eventAfterBuildFile(PostBuildSkeletonFileEvent $postBuildSkeletonFileEvent)
    {
    }

    protected function eventAfterBuildFiles(PostBuildSkeletonFilesEvent $event)
    {
    }

    protected function eventBeforeDumpFile(DumpFileEvent $event)
    {
        if ($this->isWfConfigYamlFile($event->getSkeletonFile())) {
            $content = $event->getSkeletonFile()->getContents();
            $helpComment = <<<EOS
# Available configuration parameters
# ==================================
#
# List all:
#   wf --config-dump
#
# List only names:
#   wf --config-dump --only-recipes
#
# List only a recipe:
#   wf --config-dump --recipe=symfony3
#
# Save to a file to edit:
#    wf --config-dump --no-ansi > .wf.yml
#
# Add new recipe:
#    wf --config-dump --recipe=php --no-ansi >> .wf.yml
#
# ----------------------------------------------------------------------------------------------------------------------

EOS;
            $event->getSkeletonFile()->setContents($helpComment . $content);
        }
    }

    protected function eventBeforeDumpTargetExists(DumpFileEvent $event)
    {
    }

    protected function eventAfterDumpFile(DumpFileEvent $event)
    {
        $this->printDumpedFile($event);
    }

    protected function eventSkipDumpFile(DumpFileEvent $event)
    {
    }

    protected function printDumpedFile(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        $status = SkeletonFile::HANDLE_EXISTING_APPEND == $skeletonFile->getHandleExisting()
            ? 'modified'
            : 'created'
        ;

        $this->ioManager->getOutput()->writeln(sprintf(
            '<info> ✓ The </info>%s/<comment>%s</comment><info> file has been %s.</info>',
            $skeletonFile->getRelativePath(),
            $skeletonFile->getFileName(),
            $status
        ));
    }

    protected function isWfConfigYamlFile(SkeletonFile $skeletonFile)
    {
        $filename = $skeletonFile->getFileName();
        $extension = $skeletonFile->getBaseFileInfo()->getExtension();

        if (0 !== strpos($filename, '.wf')) {
            return false;
        }

        if (\in_array($extension, ['yml', 'yaml'])
            || '.yml.dist' == substr($filename, -9)
            || '.yaml.dist' == substr($filename, -10)
        ) {
            return true;
        }

        return false;
    }
}
