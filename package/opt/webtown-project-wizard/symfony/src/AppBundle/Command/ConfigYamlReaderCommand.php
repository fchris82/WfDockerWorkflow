<?php

namespace AppBundle\Command;

use AppBundle\Configuration\Builder;
use AppBundle\Configuration\Configuration;
use AppBundle\Configuration\RecipeManager;
use AppBundle\Wizard\Helper\ComposerInstaller;
use AppBundle\Wizard\Manager;
use AppBundle\Wizard\PublicWizardInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class ConfigYamlReaderCommand.
 */
class ConfigYamlReaderCommand extends ContainerAwareCommand
{
    const DEFAULT_CONFIG_FILE = '.wf.yml';
    const DEFAULT_TARGET_DIRECTORY = '.wf';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:config')
            ->setDescription('Project config init.')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Set config file name.', self::DEFAULT_CONFIG_FILE)
            ->addOption('target-directory', null, InputOption::VALUE_REQUIRED, 'Set the build target.', self::DEFAULT_TARGET_DIRECTORY)
            ->addOption('config-hash', null, InputOption::VALUE_REQUIRED, 'Set the config hash')
            ->addArgument('base', InputArgument::OPTIONAL, 'The working directory', $_SERVER['PWD'])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDirectory = $input->getArgument('base');
        $configuration = $this->getContainer()->get(Configuration::class);
        $config = $configuration->loadConfig($input->getOption('file'), $baseDirectory);

        $builder = $this->getContainer()->get(Builder::class);
        $builder
            ->setTargetDirectory($input->getOption('target-directory'))
            ->build($config, $baseDirectory, $input->getOption('config-hash'))
        ;

        $output->writeln('<info>The (new) docker environment was build!</info>');
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
