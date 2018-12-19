<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.13.
 * Time: 16:15
 */

namespace App\Webtown\WorkflowBundle\Command;

use App\Webtown\WorkflowBundle\Environment\IoManager;
use App\Webtown\WorkflowBundle\Exception\CommanderRunException;
use App\Webtown\WorkflowBundle\Exception\Extension\ExtensionException;
use App\Webtown\WorkflowBundle\Extension\ExtensionManager;
use App\Webtown\WorkflowBundle\Wizard\Manager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\SplFileInfo;

class ExtensionHandlingCommand extends ContainerAwareCommand
{
    const EXIT_SIGN = '🢤';
    const ENABLED_SIGN = '✓';
    const DISABLED_SIGN = '∅';

    const ACTION_MANAGE = 'manage';
    const ACTION_INSTALL = 'install';
    const ACTION_UPDATE = 'update';

    /**
     * @var ExtensionManager
     */
    protected $extensionManager;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * ExtensionHandlingCommand constructor.
     *
     * @param ExtensionManager $extensionManager
     * @param Filesystem       $fileSystem
     */
    public function __construct(ExtensionManager $extensionManager, Filesystem $fileSystem, IoManager $ioManager)
    {
        $this->extensionManager = $extensionManager;
        $this->fileSystem = $fileSystem;
        $this->ioManager = $ioManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:extension')
            ->setDescription('It can install third party recipes or wizards.')
            ->addArgument('action', InputArgument::OPTIONAL, 'Action', static::ACTION_MANAGE)
            ->addArgument('source', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'List of sources.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln(' <comment>!> If the <question>CTRL-C</question> doesn\'t work, you can use the <question>^P + ^Q + ^C</question> (^ == CTRL).</comment>');
        $output->writeln('');

        switch ($input->getArgument('action')) {
            case static::ACTION_INSTALL:
                $this->handleInstall($input->getArgument('source'));
                break;
            case static::ACTION_UPDATE:
                $this->runUpdate($input->getArgument('source'));
                break;
            default:
                $this->renderNavigation();
        }
    }

    protected function renderNavigation()
    {
        $navigationQuestion = new ChoiceQuestion('What do you want to do?', [
            'q' => 'Quit',
            'l' => 'List installed extensions',
            'i' => 'Install a new extension',
            'u' => 'Update installed extension(s)',
        ]);
        $action = $this->ioManager->ask($navigationQuestion);

        switch (strtolower($action)) {
            case 'q':
                $this->ioManager->writeln('Quit');

                return;
            case 'l':
                $this->renderSummaryTable();
                break;
            case 'i':
                $this->handleInstall([]);
                break;
            case 'u':
                $this->runUpdate([]);
                break;
        }

        $this->ioManager->clearScreen();
        $this->renderNavigation();
    }

    protected function handleInstall($sources)
    {
        $io = $this->ioManager->getIo();
        if (0 == \count($sources)) {
            $question = new Question(sprintf(
                'Set the source with type. Allowed types: <comment>%s</comment>. Pattern: <comment>[type]%s[source]</comment>',
                implode('</comment>, <comment>', $this->extensionManager->getAllowedInstallerTypes()),
                ExtensionManager::SOURCE_TYPE_SEPARATOR
            ));
            $sources = [$io->askQuestion($question)];
        }

        foreach ($sources as $source) {
            try {
                $io->title('Start install: ' . $source);
                $this->extensionManager->installExtension($source);
                $io->writeln(sprintf('<info>%s</info> %s is installed.', static::ENABLED_SIGN, $source));
            } catch (ExtensionException $e) {
                $io->error($e->getMessage());
            } catch (CommanderRunException $e) {
                $io->error($e->getMessage());
            }
        }
    }

    protected function runUpdate($sources)
    {
        if (0 == \count($sources)) {
            $paths = $this->extensionManager->getAllInstalledPaths();
        }
        $this->extensionManager->fullUpdate();
    }

    // @todo (Chris) Ki kellene listázni a fejlesztés alatt lévő könyvtárakat. Tehát ami "szerepel" a gitignore fájlban, de nincs telepítve, és nem kívülről jön
    protected function renderSummaryTable()
    {
        $extensionDirectories = $this->extensionManager->getInstalledExtensions();
        if (\count($extensionDirectories)) {
            $table = new Table($this->ioManager->getIo());
            $table->setHeaders([
                'Name',
                'Type',
            ]);
            foreach ($extensionDirectories as $directory) {
                $name = $directory->getFilename();
                $icon = $this->getIcon($directory);
                $style = $this->getStyle($directory);
                $table->addRow([
                    $style ? sprintf('<%1$s>%2$s %3$s</%1s>', $style, $icon, $name) : "$icon $name",
                    basename($directory->getPath()),
                ]);
            }

            $table->render();
        } else {
            $this->ioManager->getIo()->note('There aren\'t installed extensions still.');
        }
    }

    protected function getIcon(SplFileInfo $directory)
    {
        $sourceCacheFile = $directory->getPathname() . \DIRECTORY_SEPARATOR . ExtensionManager::SOURCE_FILE_CACHE;

        if ($this->fileSystem->exists($sourceCacheFile)) {
            return static::ENABLED_SIGN;
        }

        return static::DISABLED_SIGN;
    }

    protected function getStyle(SplFileInfo $directory)
    {
        $sourceCacheFile = $directory->getPathname() . \DIRECTORY_SEPARATOR . ExtensionManager::SOURCE_FILE_CACHE;

        if ($this->fileSystem->exists($sourceCacheFile)) {
            return 'info';
        }

        return false;
    }

    protected function getSummaryQuestion(Manager $wizardManager)
    {
        $choices = [
            '' => '<comment>Exit</comment>',
        ];
        foreach ($wizardManager->getAllAvailableWizardItems() as $configurationItem) {
            $choices[$configurationItem->getClass()] = $configurationItem->getName();
        }
        $question = new ChoiceQuestion('Which one do you want to edit?', $choices, '');

        return $question;
    }

    protected function renderItemSummaryTable($class, Manager $wizardManager, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders([
            'Property',
            'Value',
        ]);
        $configurationItem = $wizardManager->getConfiguration()->get($class);
        $table->addRows([
            ['name', $configurationItem->getName()],
            ['class', $configurationItem->getClass()],
            ['group', $configurationItem->getGroup()],
            ['priority', $configurationItem->getPriority()],
        ]);
        $table->render();
    }
}
