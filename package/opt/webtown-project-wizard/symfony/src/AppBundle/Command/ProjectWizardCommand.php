<?php

namespace AppBundle\Command;

use AppBundle\Wizard\Helper\ComposerInstaller;
use AppBundle\Wizard\Manager;
use AppBundle\Wizard\WizardInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
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
        $output->writeln(' <comment>!> The <question>CTRL-C</question> doesn\'t work, you are in a docker container. You can use the <question>^P + ^Q + ^C</question> (^ == CTRL).</comment>');
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
        // GROUP
        $groupNames = array_keys($wizards);
        // Ha csak 1 csoport van, akkor rögtön "belelépünk" és nem kérdezzük meg, hogy melyiket szeretné használni.
        if (count($wizards) == 1) {
            $group = reset($groupNames);
        } else {
            $question = new ChoiceQuestion('Select group', $groupNames);
            $group = $helper->ask($input, $output, $question);
        }
        $this->writeTitle($output, $group);

        // WIZARDS
        /** @var WizardInterface $wizard */
        foreach ($wizards[$group] as $name => $wizard) {
            $output->writeln(sprintf('<info>%s</info>', $wizard->getName()));
            $output->writeln(sprintf('    %s', $wizard->getInfo()));
            $output->writeln('');
        }

        $wizardNames = array_keys($wizards[$group]);
        // Ha az adott csoportban csak 1 Wizard van, akkor azt elindítjuk.
        if (count($wizardNames) == 1) {
            $selected = [reset($wizardNames)];
        } else {
            $question = new ChoiceQuestion('Select wizard', $wizardNames);
            // @todo (Chris) Ezt lehet, hogy törölni kellene, mivel néhány Wizard ütközhet, ha egymás után hívjuk.
            $question->setMultiselect(true);
            $selected = $helper->ask($input, $output, $question);
        }

        // BUILDS
        foreach ($selected as $wizardName) {
            $wizard = $wizards[$group][$wizardName];
            $wizard
                ->setCommand($this)
                ->setInput($input)
                ->setOutput($output);

            $this->writeTitle($output, sprintf('[%s] %s', $group, $wizardName));
            $output->writeln($wizard->getInfo());

            // @todo (Chris) Ez ne egy fix könyvtárra mutasson!
            $targetProjectDirectory = $wizard->build($_SERVER['PWD']);

            // A composer require paranccsal bekötjök azokat a programokat, amik szükségesek
            ComposerInstaller::installComposerPackages($targetProjectDirectory, $wizard->getComposerPackages(), $output);
        }
    }

    protected function writeTitle(OutputInterface $output, $title, $colorStyle = 'comment')
    {
        // 2 sort kihagyunk
        $output->writeln("\n");
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, $title));
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, str_repeat('=', strlen($title))));
        $output->writeln('');
    }
}
