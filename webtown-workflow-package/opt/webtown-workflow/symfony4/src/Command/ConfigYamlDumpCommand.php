<?php

namespace App\Command;

use App\Configuration\Configuration;
use App\Configuration\RecipeManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Dumper\YamlReferenceDumper;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addOption('recipe', null, InputOption::VALUE_OPTIONAL, 'You have to chose a recipe', null)
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

        // We show it if the user don't want to put it into a file!
        if ($io->isDecorated()) {
            $io->title('All available parameters');
            $io->writeln('  Use the <info>--no-ansi</info> argument if you want to put it into a file!');
        }

        $dumper = new YamlReferenceDumper();

        $baseConfiguration = $this->getContainer()->get(Configuration::class);
        if ($recipeNameOrClass = $input->getOption('recipe')) {
            $recipe = $this->getRecipeByNameOrClass($recipeNameOrClass);
            /** @var ArrayNode $rootNode */
            $rootNode = $recipe->getConfig();
            $io->write($dumper->dumpNode($rootNode->getNode(true)));
        } else {
            /** @var ArrayNode $rootNode */
            $rootNode = $baseConfiguration->getConfigTreeBuilder()->buildTree();
            // Show only the children
            foreach ($rootNode->getChildren() as $node) {
                $io->write($dumper->dumpNode($node));
            }
        }
    }

    /**
     * @param string $nameOrClass
     *
     * @return \Recipes\BaseRecipe
     */
    protected function getRecipeByNameOrClass($nameOrClass)
    {
        $recipeManager = $this->getContainer()->get(RecipeManager::class);
        $altFqn = sprintf('Recipes\%s\Recipe', $nameOrClass);
        foreach ($recipeManager->getRecipes() as $recipe) {
            if (get_class($recipe) == $nameOrClass
                || get_class($recipe) == $altFqn
                || $recipe->getName() == $nameOrClass
            ) {
                return $recipe;
            }
        }

        throw new InvalidArgumentException(sprintf('Missing or wrong recipe: `%s`', $nameOrClass));
    }
}
