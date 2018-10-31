<?php

namespace App\Command;

use App\Configuration\Builder;
use App\Configuration\Configuration;
use App\Configuration\RecipeManager;
use App\Event\ConfigurationEvents;
use App\Event\DumpEvent;
use App\Event\VerboseInfoEvent;
use App\Exception\InvalidWfVersionException;
use App\Exception\MissingRecipeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigYamlReaderCommand.
 */
class ConfigYamlReaderCommand extends ContainerAwareCommand
{
    const DEFAULT_CONFIG_FILE = '.wf.yml';
    const DEFAULT_TARGET_DIRECTORY = '.wf';

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

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
            $this->input = $input;
            $this->output = $output;

            $baseDirectory = $input->getArgument('base');
            /** @var Configuration $configuration */
            $configuration = $this->getContainer()->get(Configuration::class);
            $config = $configuration->loadConfig($input->getOption('file'), $baseDirectory, $input->getOption('wf-version'));

            $this->registerEventListeners($input, $output);

            /** @var Builder $builder */
            $builder = $this->getContainer()->get(Builder::class);
            try {
                $builder
                    ->setTargetDirectory($input->getOption('target-directory'))
                    ->build($config, $baseDirectory, $input->getOption('config-hash'))
                ;

                $output->writeln('<info>The (new) docker environment was build!</info>');

            // It is maybe an impossible exception, but it will throw we catch it.
            } catch (MissingRecipeException $e) {
                $output->writeln('<comment>' . $e->getMessage() . '</comment>');
                $output->writeln('The available recipes:');
                foreach ($this->getContainer()->get(RecipeManager::class)->getRecipes() as $recipe) {
                    $output->writeln(sprintf('  - <info>%s</info> @%s', $recipe->getName(), get_class($recipe)));
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
        $output->writeln(sprintf('<%1$s>%2$s</%1$s>', $colorStyle, str_repeat('=', strlen(strip_tags($title)))));
        $output->writeln('');
    }

    /**
     * @todo (Chris) Esetleg ezt az egész eseménykezelőst dolgot áthelyezhetnénk egy külön service-be, ami set-tel megkapja az input és output értékeket, majd az alapján cselekszik.
     * Registering event listeners.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function registerEventListeners(InputInterface $input, OutputInterface $output)
    {
        $eventDispatcher = $this->getContainer()->get('event_dispatcher');
        if ($output->isVerbose()) {
            $eventDispatcher->addListener(
                ConfigurationEvents::VERBOSE_INFO,
                [$this, 'verboseInfo']
            );
        }
        $eventDispatcher->addListener(
            ConfigurationEvents::BEFORE_DUMP,
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
        if (is_array($info)) {
            $info = Yaml::dump($info, 4);
        }
        $this->output->writeln($info);
    }

    /**
     * Add warnings to almost all configured file.
     *
     * @param DumpEvent $dumpEvent
     */
    public function insertGeneratedFileWarning(DumpEvent $dumpEvent)
    {
        $warning = sprintf(
            'This is an auto generated file from `%s` config file! You shouldn\'t edit this.',
            $this->input->getOption('file')
        );
        $ext = pathinfo($dumpEvent->getTargetPath(), PATHINFO_EXTENSION);

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

        $newContents = sprintf($commentPattern, $warning) . $dumpEvent->getContents();
        $dumpEvent->setContents($newContents);
    }
}
