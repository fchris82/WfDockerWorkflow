<?php

namespace App\Command;

use App\Configuration\Configuration;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\ArrayNode;
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
            ->setDescription('Project config dump. Use the <comment>--no-ansi</comment> argument if you want to put it into a file!')
            ->setHelp('Use the <info>--no-ansi</info> argument if you want to put it into a file!')
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

        // We show it if the user don't want to put it into a file!
        if ($io->isDecorated()) {
            $io->title('All available parameters');
            $io->writeln('  Use the <info>--no-ansi</info> argument if you want to put it into a file!');
        }

        /** @var ArrayNode $rootNode */
        $rootNode = $configuration->getConfigTreeBuilder()->buildTree();
        // Show only the children
        foreach ($rootNode->getChildren() as $node) {
            $io->write($dumper->dumpNode($node));
        }
    }
}
