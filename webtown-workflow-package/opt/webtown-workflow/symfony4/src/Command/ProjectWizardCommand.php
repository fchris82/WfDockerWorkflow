<?php

namespace App\Command;

use App\Exception\WizardSomethingIsRequiredException;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wizards\BaseWizard;
use App\Wizard\Manager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ProjectWizardCommand.
 */
class ProjectWizardCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:wizard')
            ->addOption('wf-version', null, InputOption::VALUE_REQUIRED, 'Set the current WF version')
            ->addOption('target-dir', null, InputOption::VALUE_OPTIONAL, 'The working directory.', $_SERVER['PWD'])
            ->addOption('force', null, InputOption::VALUE_NONE, 'If it is set, the program won\'t check the requires, and you can use all available wizards.')
            ->addOption('full', null, InputOption::VALUE_NONE, 'If it is set, the program will list all installed wizards! Include disableds too.')
            ->setDescription('Wizard collection handler command.')
            ->setHelp(<<<EOS
You can run wizards. You can enable or disable wizards with the <comment>wizard --config</comment> command. The <comment>wizard</comment> and
the <comment>wizard --config</comment> command are aliases:
    <info>wizard</info>             <comment>php bin/console app:wizard</comment> 
    <info>wizard --config</info>    <comment>php bin/console app:wizard:config</comment>    See: <comment>wizard --config --help</comment>

Examples:
    <comment>wizard --full</comment>
    List all installed wizards, the disabled wizards too.
    
    <comment>wizard --force</comment>
    List all <info>enabled</info> wizards without requires or built check.
    
    <comment>wizard --force --full</comment>
    List all installed wizards. 
    
    <comment>wizard --config</comment>
    You can configure your installed wizards. You can change the visible <info>names</info>, <info>groups</info>, <info>priority</info>
    and <info>availability</info> (enabled/disabled). 
    
    <comment>wizard <fg=cyan>--dev</></comment>
    The <fg=cyan>--dev</> switch develop debug mode on. 
    
    <comment>wizard <fg=cyan>--dev</> --config</comment>
    Run config command with develop debug mode.
EOS
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $output->writeln('');
        $output->writeln(' <comment>!> If the <question>CTRL-C</question> doesn\'t work, you can use the <question>^P + ^Q + ^C</question> (^ == CTRL).</comment>');
        $output->writeln(' <comment>!> You can edit the enabled wizards and sort order with the <info>wizard --config</info> command.</comment>');

        // @todo (Chris) Ezt inkább option-ből beszedni!
        $targetProjectDirectory = $input->getOption('target-dir');
        $isForce = $input->getOption('force');
        $isFull = $input->getOption('full');

        $wizardManager = $this->getContainer()->get(Manager::class);
        $enabledWizards = $isFull ? $wizardManager->getAllWizards() : $wizardManager->getAllEnabledWizards();

        if (count($enabledWizards) == 0) {
            $this->writeNote($io, 'There isn\'t any installed/enabled wizard! The program exited.');

            return;
        }

        $io->block(
            'You can see information about all available wizards. If the name is yellow the program will disable it. The reason would be there are missing requires OR it has already built/run. Use the <comment>--force</comment> option to disable this check. You can read more information with <comment>wizard --help</comment> command.',
            null,
            null,
            ' ',
            false,
            false
        );

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $choices = [];
        $wizardChoices = [];
        foreach ($enabledWizards as $configurationItem) {
            /** @var BaseWizard $wizard */
            $wizard = $this->getContainer()->get($configurationItem->getClass());
            $missingRequires = false;
            $built = false;
            if (!$isForce) {
                try {
                    $wizard->checkRequires($targetProjectDirectory);
                    if ($wizard->isBuilt($targetProjectDirectory)) {
                        $built = true;
                    }
                } catch (WizardSomethingIsRequiredException $e) {
                    $missingRequires = $e->getMessage();
                }
            }
            $groupPrefix = $configurationItem->getGroup() ?
                sprintf('<comment>[%s]</comment> ', $configurationItem->getGroup()) :
                '';
            $io->writeln(sprintf(
                '  <%1$s>%2$s%3$s</%1$s>',
                $built || $missingRequires ? 'comment' : 'info',
                $groupPrefix,
                $configurationItem->getName()
            ));
            $io->writeln(sprintf('    %s', $wizard->getInfo()));
            $io->newLine();

            if (!$missingRequires && !$built) {
                $key = sprintf('%s%s', $groupPrefix, $configurationItem->getName());
                $choices[] = $key;
                $wizardChoices[$key] = $wizard;
            }
        }

        if (count($choices) > 0) {
            $question = new ChoiceQuestion('Select wizard (multiselect!)', $choices);
            // @todo (Chris) Ezt lehet, hogy törölni kellene, mivel néhány Wizard ütközhet, ha egymás után hívjuk.
            $question->setMultiselect(true);
            $selected = $helper->ask($input, $output, $question);

            // BUILDS
            foreach ($selected as $key) {
                /** @var BaseWizard $wizard */
                $wizard = $wizardChoices[$key];
                $wizard
                    ->setCommand($this)
                    ->setInput($input)
                    ->setOutput($output);

                $io->title($key);
                $output->writeln($wizard->getInfo());

                $targetProjectDirectory = $wizard->runBuild($targetProjectDirectory);
            }
        } else {
            $this->writeNote($io, 'There isn\'t any callable wizard! The program exited. You can use the `--force` or `--full` arguments.');
        }
    }

    protected function writeNote(SymfonyStyle $io, $note, $colorStyle = 'fg=white;bg=yellow;options=bold')
    {
        $io->block($note, 'NOTE', $colorStyle, ' ', true);
    }
}
