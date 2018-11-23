<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 16:05.
 */

namespace App\Wizard;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Wizards\BaseWizard;

/**
 * Class Manager.
 *
 * Ezzel a Manager-rel kezeljük igazából a `wizard` taget a service-ek kapcsán. Itt gyűjtjük össze és itt rendezzük az
 * elérhető Wizard service-eket.
 */
class Manager implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @var string
     */
    protected $wizardsPath;

    /**
     * @var WizardInterface[]
     */
    protected $wizards;

    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var bool
     */
    protected $configurationIsSynced = false;

    /**
     * RecipeManager constructor.
     *
     * @param string $wizardBaseDir
     */
    public function __construct($wizardBaseDir, Configuration $configuration)
    {
        $this->wizardsPath = $wizardBaseDir;
        $this->configuration = $configuration;
    }

    /**
     * @return WizardInterface[]
     */
    public function findAllWizards()
    {
        if (!$this->wizards) {
            $finder = new Finder();
            $finder
                ->in($this->wizardsPath)
                ->name('*Wizard.php')
                ->exclude('skeletons')
                ->depth(1)
            ;
            $this->wizards = [];
            /** @var SplFileInfo $wizardFile */
            foreach ($finder as $wizardFile) {
                // Skip the files in route!
                if ($wizardFile->getRelativePath() == '') {
                    continue;
                }
                $fullClass = sprintf(
                    'Wizards\\%s\\%s',
                    str_replace('/', '\\', $wizardFile->getRelativePath()),
                    $wizardFile->getBasename('.php')
                );
                /** @var WizardInterface $wizard */
                $wizard = $this->container->get($fullClass);
                if (array_key_exists($wizard->getDefaultName(), $this->wizards)) {
                    throw new InvalidConfigurationException(sprintf(
                        'The `%s` recipe has been already existed! [`%s` vs `%s`]',
                        $wizard->getDefaultName(),
                        get_class($this->wizards[$wizard->getDefaultName()]),
                        get_class($wizard)
                    ));
                }
                if (!$wizard->isHidden()) {
                    $this->wizards[$wizard->getDefaultName()] = $wizard;
                }
            }
        }

        return $this->wizards;
    }

    public function syncConfiguration() {
        if (!$this->configurationIsSynced) {
            $wizards = $this->findAllWizards();
            foreach ($wizards as $installedWizard) {
                if (!$this->configuration->has($installedWizard)) {
                    $configurationItem = new ConfigurationItem(
                        $installedWizard,
                        $installedWizard->getDefaultName(),
                        !$installedWizard->isHidden(),
                        $installedWizard->getDefaultGroup()
                    );
                    $this->configuration->add($configurationItem);
                }
            }
            foreach ($this->configuration->getConfigurationList() as $configurationItem) {
                $exists = false;
                foreach ($wizards as $wizard) {
                    if ($configurationItem->getClass() == get_class($wizard)) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $this->configuration->remove($configurationItem);
                }
            }

            $this->configurationIsSynced = true;
        }
    }

    /**
     * @return array|ConfigurationItem[]
     */
    public function getAllWizards()
    {
        $this->syncConfiguration();

        return $this->configuration->getConfigurationList();
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return array|ConfigurationItem[]
     */
    public function getAllEnabledWizards()
    {
        $this->syncConfiguration();

        return $this->configuration->getAllEnabled();
    }

    public function getConfigurationUnsavedChanges($changeType = null)
    {
        return $this->configuration->getChanges($changeType);
    }

    public function wizardIsNew($wizardOrClass)
    {
        return $this->wizardIs(Configuration::CHANGES_ADDED, $wizardOrClass);
    }

    public function wizardIsUpdated($wizardOrClass)
    {
        return $this->wizardIs(Configuration::CHANGES_UPDATED, $wizardOrClass);
    }

    public function wizardIsRemoved($wizardOrClass)
    {
        return $this->wizardIs(Configuration::CHANGES_REMOVED, $wizardOrClass);
    }

    protected function wizardIs($changeType, $wizardOrClass)
    {
        $class = $wizardOrClass;
        if (is_object($wizardOrClass)) {
            if ($wizardOrClass instanceof ConfigurationItem) {
                $class = $wizardOrClass->getClass();
            } else {
                $class = get_class($wizardOrClass);
            }
        }

        foreach ($this->configuration->getChanges($changeType) as $newConfigurationItem) {
            if ($newConfigurationItem->getClass() == $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $recipeName
     *
     * @return BaseRecipe
     *
     * @throws MissingRecipeException
     */
    public function getRecipe($recipeName)
    {
        $recipes = $this->getWizards();
        if (!array_key_exists($recipeName, $recipes)) {
            throw new MissingRecipeException(sprintf('The `%s` recipe is missing!', $recipeName));
        }

        return $recipes[$recipeName];
    }
}
