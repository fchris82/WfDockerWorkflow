<?php

namespace AppBundle\Command;

use AppBundle\Configuration\Configuration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ConfigYamlDumpCommand.
 */
class ConfigYamlDumpCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:config-dump')
            ->setDescription('Project config dump.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Console\Exception\RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $configuration = $this->getContainer()->get(Configuration::class);
        $dumper = new YamlReferenceDumper();

        $io->writeln($dumper->dump($configuration));
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
