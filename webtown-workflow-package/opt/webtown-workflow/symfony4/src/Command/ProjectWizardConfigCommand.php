<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.13.
 * Time: 16:15
 */

namespace App\Command;

use App\Wizard\Configuration;
use App\Wizard\ConfigurationItem;
use App\Wizard\Manager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProjectWizardConfigCommand extends ContainerAwareCommand
{
    const EXIT_SIGN = '🢤';
    const ENABLED_SIGN = '✓';
    const DISABLED_SIGN = '∅';

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
            ->setName('app:wizard:config')
            ->setDescription('Wizard collection configuration.');
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

        $wizardManager = $this->getContainer()->get(Manager::class);
        $wizardManager->syncConfiguration();

        do {
            $this->renderSummaryTable($wizardManager);
            $summaryQuestion = $this->getSummaryQuestion($wizardManager);
            if ($selectedClass = $this->questionHelper->ask($input, $output, $summaryQuestion)) {
                $this->editConfigItem($selectedClass, $wizardManager, $input, $output);
            }
        } while ($selectedClass);

        if ($wizardManager->getConfiguration()->hasChanges()) {
            $doYouWantToSaveQuestion = new ConfirmationQuestion('There are some changes. Do you want to save them?', true);
            $wantToSave = $this->io->askQuestion($doYouWantToSaveQuestion);
            if ($wantToSave) {
                $wizardManager->getConfiguration()->saveConfigurationList();
                $this->io->success('Wizard configuration is updated! (Default location on host: ~/.webtown-workflow/config/wizards.yml)');
            } else {
                $this->io->writeln('Nothing changed');
            }
        }
    }

    protected function writeTitle(OutputInterface $output, $title, $colorStyle = 'fg=white')
    {
        // 2 sort kihagyunk
        $output->writeln("\n");
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, $title));
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, str_repeat('=', strlen(strip_tags($title)))));
        $output->writeln('');
    }

    protected function getIcon(ConfigurationItem $configurationItem, Manager $wizardManager)
    {
        if ($configurationItem->isEnabled()) {
            return static::ENABLED_SIGN;
        }

        return static::DISABLED_SIGN;
    }

    protected function getStyle(ConfigurationItem $configurationItem, Manager $wizardManager)
    {
        if ($wizardManager->wizardIsNew($configurationItem)) {
            return 'info';
        }
        if ($wizardManager->wizardIsUpdated($configurationItem)) {
            return 'comment';
        }

        return null;
    }

    protected function renderSummaryTable(Manager $wizardManager)
    {
        $table = new Table($this->io);
        $table->setHeaders([
            'Name',
            'Group',
            'Priority',
        ]);
        foreach ($wizardManager->getAllWizards() as $configurationItem) {
            $name = $configurationItem->getName();
            $icon = $this->getIcon($configurationItem, $wizardManager);
            $style = $this->getStyle($configurationItem, $wizardManager);
            $table->addRow([
                $style ? sprintf('<%1$s>%2$s %3$s</%1s>', $style, $icon, $name) : "$icon $name",
                $configurationItem->getGroup(),
                $configurationItem->getPriority(),
            ]);
        }
        foreach ($wizardManager->getConfiguration()->getChanges(Configuration::CHANGES_REMOVED) as $configurationItems) {
            /** @var ConfigurationItem $configurationItem */
            foreach ($configurationItems as $configurationItem) {
                $table->addRow([
                    sprintf('<warning>❌ %s</warning>', $configurationItem->getClass()),
                    $configurationItem->getGroup(),
                    $configurationItem->getPriority(),
                ]);
            }
        }
        $table->render();
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
            'Value'
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
                    'handle' => function(ConfigurationItem $configurationItem, $name) {
                        $configurationItem->setName($name);
                    },
                ],
                'group' => [
                    'question' => $groupQuestion,
                    'handle' => function(ConfigurationItem $configurationItem, $group) {
                        $configurationItem->setGroup($group);
                    },
                ],
                'priority' => [
                    'question' => $priorityQuestion,
                    'handle' => function(ConfigurationItem $configurationItem, $priority) {
                        $configurationItem->setPriority($priority);
                    },
                ],
                'enabled' => [
                    'question' => new ConfirmationQuestion('Wizard is enabled? ', $configurationItem->isEnabled()),
                    'handle' => function(ConfigurationItem $configurationItem, $enabled) {
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
                if (is_bool($label)) {
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
        } while($change != static::EXIT_SIGN);

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
