<?php

namespace App\Command;

use App\Wizard\BaseWizard;
use App\Wizard\Helper\ComposerInstaller;
use App\Wizard\Manager;
use App\Wizard\PublicWizardInterface;
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
            ->setDescription('Wizard collection handler command.');
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

        $wizardManager = $this->getContainer()->get(Manager::class);

        /**
         * Lekérjük a wizard-okat:
         * <code>
         *  $wizards = [
         *      'group1' => [
         *          'name1' => $wizard1,
         *          'name2' => $wizard2,
         *      ],
         *      'group2' => [
         *          'name3' => $wizard3,
         *          'name1' => $wizard1,
         *      ],
         *  ]
         * <code>.
         */
        $wizards = $wizardManager->getWizards();

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $choices = [];
        $wizardChoices = [];
        foreach ($wizards as $group => $groupWizards) {
            /** @var PublicWizardInterface $wizard */
            foreach ($groupWizards as $name => $wizard) {
                $output->writeln(sprintf('  <comment>[%s]</comment> <info>%s</info>', $group, $wizard->getName()));
                $output->writeln(sprintf('    %s', $wizard->getInfo()));
                $output->writeln('');

                $key = sprintf('<comment>[%s]</comment> %s', $group, $wizard->getName());
                $choices[] = $key;
                $wizardChoices[$key] = $wizard;
            }
        }

        $question = new ChoiceQuestion('Select wizard (multiselect!)', $choices);
        // @todo (Chris) Ezt lehet, hogy törölni kellene, mivel néhány Wizard ütközhet, ha egymás után hívjuk.
        $question->setMultiselect(true);
        $selected = $helper->ask($input, $output, $question);

        // BUILDS
        foreach ($selected as $key) {
            /** @var BaseWizard|PublicWizardInterface $wizard */
            $wizard = $wizardChoices[$key];
            $wizard
                ->setCommand($this)
                ->setInput($input)
                ->setOutput($output);

            $this->writeTitle($output, $key);
            $output->writeln($wizard->getInfo());

            // @todo (Chris) Ez ne egy fix könyvtárra mutasson!
            $targetProjectDirectory = $wizard->build($_SERVER['PWD']);

            // A composer require paranccsal bekötjök azokat a programokat, amik szükségesek
            $wizard->installComposerPackages($targetProjectDirectory);
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
}
