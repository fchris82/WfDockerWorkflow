<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.13.
 * Time: 16:15
 */

namespace App\Command;

use App\Exception\CommanderRunException;
use App\Exception\Extension\ExtensionException;
use App\Extension\ExtensionInterface;
use App\Extension\ExtensionManager;
use App\Wizard\Configuration;
use App\Wizard\ConfigurationItem;
use App\Wizard\Manager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\SplFileInfo;

class ExtensionHandlingCommand extends ContainerAwareCommand implements InteractiveCommandInterface
{
    const EXIT_SIGN = '🢤';
    const ENABLED_SIGN = '✓';
    const DISABLED_SIGN = '∅';

    const ACTION_MANAGE = 'manage';
    const ACTION_INSTALL = 'install';
    const ACTION_UPDATE = 'update';

    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:extension')
            ->setDescription('It can install third party recipes or wizards.')
            ->addArgument('action', InputArgument::OPTIONAL, 'Action', static::ACTION_MANAGE)
            ->addArgument('source', InputArgument::OPTIONAL|InputArgument::IS_ARRAY, 'List of sources.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->questionHelper = $this->getHelper('question');

        $output->writeln('');
        $output->writeln(' <comment>!> If the <question>CTRL-C</question> doesn\'t work, you can use the <question>^P + ^Q + ^C</question> (^ == CTRL).</comment>');
        $output->writeln('');

        /** @var ExtensionManager $extensionManager */
        $extensionManager = $this->getContainer()->get(ExtensionManager::class);

        switch ($input->getArgument('action')) {
            case static::ACTION_INSTALL:
                $this->handleInstall($input->getArgument('source'), $extensionManager);
                break;
            case static::ACTION_UPDATE:
                $this->runUpdate($input->getArgument('source'), $extensionManager);
                break;
            default:
                $this->renderNavigation();
        }
    }

    protected function renderNavigation()
    {
        // @todo (Chris)
    }

    protected function handleInstall($sources, ExtensionManager $extensionManager)
    {
        if (count($sources) == 0) {
            $question = new Question(sprintf(
                'Set the source with type. Allowed types: <comment>%s</comment>. Pattern: <comment>[type]%s[source]</comment>',
                implode('</comment>, <comment>', $extensionManager->getAllowedInstallerTypes()),
                ExtensionManager::SOURCE_TYPE_SEPARATOR
            ));
            $sources= [$this->io->ask($question)];
        }

        foreach ($sources as $source) {
            try {
                $this->io->title('Start install: ' . $source);
                $extensionManager->installExtension($source);
                $this->io->writeln(sprintf('<info>%s</info> %s is installed.', static::ENABLED_SIGN, $source));
            } catch (ExtensionException $e) {
                $this->io->error($e->getMessage());
            } catch (CommanderRunException $e) {
                $this->io->error($e->getMessage());
            }
        }
    }

    protected function runUpdate($sources, ExtensionManager $extensionManager)
    {
        if (count($sources) == 0) {
            $paths = $extensionManager->getAllInstalledPaths();
        }
        $extensionManager->fullUpdate();
    }

    // @todo (Chris) Ki kellene listázni a fejlesztés alatt lévő könyvtárakat. Tehát ami "szerepel" a gitignore fájlban, de nincs telepítve, és nem kívülről jön
    protected function renderSummaryTable(ExtensionManager $extensionManager)
    {
        $table = new Table($this->io);
        $table->setHeaders([
            'Name',
            'Type',
        ]);
        foreach ($extensionManager->getInstalledExtensions() as $directory) {
            $name = $directory->getFilename();
            $icon = $this->getIcon($directory, $extensionManager);
            $style = $this->getStyle($directory, $extensionManager);
            $table->addRow([
                $style ? sprintf('<%1$s>%2$s %3$s</%1s>', $style, $icon, $name) : "$icon $name",
                basename($directory->getPath()),
            ]);
        }

        $table->render();
    }

    protected function getIcon(SplFileInfo $directory, ExtensionManager $extensionManager)
    {
        $sourceCacheFile = $directory->getPathname() . DIRECTORY_SEPARATOR . ExtensionManager::SOURCE_FILE_CACHE;

        if ($this->getContainer()->get('filesystem')->exists($sourceCacheFile)) {
            return static::ENABLED_SIGN;
        }

        return static::DISABLED_SIGN;
    }

    protected function getStyle(SplFileInfo $directory, ExtensionManager $extensionManager)
    {
        $sourceCacheFile = $directory->getPathname() . DIRECTORY_SEPARATOR . ExtensionManager::SOURCE_FILE_CACHE;

        if ($this->getContainer()->get('filesystem')->exists($sourceCacheFile)) {
            return 'info';
        }

        return false;
    }

    protected function getSummaryQuestion(Manager $wizardManager)
    {
        $choices = [
            '' => '<comment>Exit</comment>',
        ];
        foreach ($wizardManager->getAllWizards() as $configurationItem) {
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

    protected function clearScreen(OutputInterface $output)
    {
//        $output->write(sprintf("\033\143"));
        $output->write("\n\n\n");
    }

    protected function editConfigItem($wizardClass, Manager $wizardManager, InputInterface $input, OutputInterface $output)
    {
        $this->clearScreen($output);
        $configurationItem = $wizardManager->getConfiguration()->get($wizardClass);
        $this->io->title($wizardClass);
        $originalContent = serialize($configurationItem);

        do {
            $priorityQuestion = new Question('Priority: ', $configurationItem->getPriority());
            $priorityQuestion->setValidator(function ($value) {
                if (!preg_match('/^\d*$/', $value)) {
                    throw new InvalidArgumentException(sprintf('The `%s` value is invalid at priority! You have to use only numbers!', $value));
                }

                return (int) $value;
            });
            $groupQuestion = new Question('Group: ', $configurationItem->getGroup());
            $groupQuestion->setAutocompleterValues($this->getAllExistingGroups($wizardManager));

            $config = [
                'name' => [
                    'question' => new Question('Name: ', $configurationItem->getName()),
                    'handle' => function (ConfigurationItem $configurationItem, $name) {
                        $configurationItem->setName($name);
                    },
                ],
                'group' => [
                    'question' => $groupQuestion,
                    'handle' => function (ConfigurationItem $configurationItem, $group) {
                        $configurationItem->setGroup($group);
                    },
                ],
                'priority' => [
                    'question' => $priorityQuestion,
                    'handle' => function (ConfigurationItem $configurationItem, $priority) {
                        $configurationItem->setPriority($priority);
                    },
                ],
                'enabled' => [
                    'question' => new ConfirmationQuestion('Wizard is enabled? ', $configurationItem->isEnabled()),
                    'handle' => function (ConfigurationItem $configurationItem, $enabled) {
                        $configurationItem->setEnabled($enabled);
                    },
                ],
            ];
            $questions = [
                static::EXIT_SIGN => '<comment>Go back</comment>',
            ];
            foreach ($config as $n => $item) {
                /** @var Question $itemQuestion */
                $itemQuestion = $item['question'];
                $label = $itemQuestion->getDefault();
                if (\is_bool($label)) {
                    $label = $label ? static::ENABLED_SIGN : static::DISABLED_SIGN;
                }
                $questions[$n] = (string) $label;
            }
            $question = new ChoiceQuestion('What do you want to change?', $questions, static::EXIT_SIGN);

            if ('🢤' != $change = $this->questionHelper->ask($input, $output, $question)) {
                /** @var Question $question */
                $subQuestion = $config[$change]['question'];
                $newValue = $this->questionHelper->ask($input, $output, $subQuestion);
                $config[$change]['handle']($configurationItem, $newValue);
            }
        } while ($change != static::EXIT_SIGN);

        // If something was changed
        if ($originalContent != serialize($configurationItem)) {
            $wizardManager->getConfiguration()->set($configurationItem);
        }

        $this->clearScreen($output);
    }

    protected function getAllExistingGroups(Manager $wizardManager)
    {
        $existingGroups = [];
        foreach ($wizardManager->getConfiguration()->getConfigurationList() as $configurationItem) {
            $existingGroups[] = $configurationItem->getGroup();
        }
        array_unique($existingGroups);

        return $existingGroups;
    }
}
