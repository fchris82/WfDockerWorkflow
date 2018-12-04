<?php

namespace App\Command;

use App\Configuration\Builder;
use App\Configuration\Configuration;
use App\Configuration\RecipeManager;
use App\Environment\IoManager;
use App\Event\Configuration\VerboseInfoEvent;
use App\Event\ConfigurationEvents;
use App\Event\SkeletonBuild\DumpFileEvent;
use App\Event\SkeletonBuildBaseEvents;
use App\Exception\InvalidWfVersionException;
use App\Exception\MissingRecipeException;
use App\Recipes\BaseRecipe;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigYamlReaderCommand.
 */
class ReconfigureCommand extends Command
{
    const DEFAULT_CONFIG_FILE = '.wf.yml';
    const DEFAULT_TARGET_DIRECTORY = '.wf';

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var RecipeManager
     */
    protected $recipeManager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var IoManager
     */
    protected $ioManager;

    /**
     * ReconfigureCommand constructor.
     *
     * @param Configuration            $configuration
     * @param Builder                  $builder
     * @param RecipeManager            $recipeManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param IoManager                $ioManager
     */
    public function __construct(
        Configuration $configuration,
        Builder $builder,
        RecipeManager $recipeManager,
        EventDispatcherInterface $eventDispatcher,
        IoManager $ioManager
    ) {
        $this->configuration = $configuration;
        $this->builder = $builder;
        $this->recipeManager = $recipeManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->ioManager = $ioManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:config')
            ->setDescription('Project config init. Generate the "cache" files.')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Set config file name.', self::DEFAULT_CONFIG_FILE)
            ->addOption('target-directory', null, InputOption::VALUE_REQUIRED, 'Set the build target.', self::DEFAULT_TARGET_DIRECTORY)
            ->addOption('config-hash', null, InputOption::VALUE_REQUIRED, 'Set the config hash')
            ->addOption('wf-version', null, InputOption::VALUE_REQUIRED, 'Set the current WF version')
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
        try {
            $baseDirectory = $input->getArgument('base');
            $config = $this->configuration->loadConfig($input->getOption('file'), $baseDirectory, $input->getOption('wf-version'));

            $this->registerEventListeners();

            try {
                $this->builder
                    ->setTargetDirectory($input->getOption('target-directory'))
                    ->build($config, $baseDirectory, $input->getOption('config-hash'))
                ;

                $output->writeln('<info>The (new) docker environment was build!</info>');
            } catch (MissingRecipeException $e) {
                // It is maybe an impossible exception, but it will throw we catch it.
                $output->writeln('<comment>' . $e->getMessage() . '</comment>');
                $output->writeln('The available recipes:');
                /** @var BaseRecipe $recipe */
                foreach ($this->recipeManager->getRecipes() as $recipe) {
                    $output->writeln(sprintf('  - <info>%s</info> @%s', $recipe->getName(), \get_class($recipe)));
                }
            }
        } catch (InvalidWfVersionException $e) {
            // We write it formatted
            $output->writeln('');
            $output->writeln($e->getMessage());
            $output->writeln('');
        }
    }

    protected function writeTitle(OutputInterface $output, $title, $colorStyle = 'fg=white')
    {
        // 2 sort kihagyunk
        $output->writeln("\n");
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, $title));
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, str_repeat('=', \strlen(strip_tags($title)))));
        $output->writeln('');
    }

    /**
     * @todo (Chris) Esetleg ezt az egész eseménykezelőst dolgot áthelyezhetnénk egy külön service-be, ami set-tel megkapja az input és output értékeket, majd az alapján cselekszik.
     * Registering event listeners.
     */
    protected function registerEventListeners()
    {
        if ($this->ioManager->getOutput()->isVerbose()) {
            $this->eventDispatcher->addListener(
                ConfigurationEvents::VERBOSE_INFO,
                [$this, 'verboseInfo']
            );
        }
        $this->eventDispatcher->addListener(
            SkeletonBuildBaseEvents::BEFORE_DUMP_FILE,
            [$this, 'insertGeneratedFileWarning']
        );
    }

    /**
     * Print verbose informations
     *
     * @param VerboseInfoEvent $event
     */
    public function verboseInfo(VerboseInfoEvent $event)
    {
        $info = $event->getInfo();
        if (\is_array($info)) {
            $info = Yaml::dump($info, 4);
        }
        $this->ioManager->writeln($info);
    }

    /**
     * Add warnings to almost all configured file.
     *
     * @param DumpFileEvent $event
     */
    public function insertGeneratedFileWarning(DumpFileEvent $event)
    {
        $skeletonFile = $event->getSkeletonFile();
        $warning = sprintf(
            'This is an auto generated file from `%s` config file! You shouldn\'t edit this.',
            $this->ioManager->getInput()->getOption('file')
        );
        $ext = pathinfo($skeletonFile->getFullTargetPathname(), PATHINFO_EXTENSION);

        $commentPattern = "# %s\n\n";
        switch ($ext) {
            case 'css':
                $commentPattern = "/* %s */\n\n";
                break;
            case 'md':
            case 'sh':
                // We skip these
                return;
        }

        $newContents = sprintf($commentPattern, $warning) . $skeletonFile->getContents();
        $skeletonFile->setContents($newContents);
    }
}
