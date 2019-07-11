<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 16:05.
 */

namespace App\Webtown\WorkflowBundle\Wizard;

/**
 * Class Manager.
 *
 * Ezzel a Manager-rel kezeljük igazából a `wizard` taget a service-ek kapcsán. Itt gyűjtjük össze és itt rendezzük az
 * elérhető Wizard service-eket.
 */
class Manager
{
    /**
     * @var WizardInterface[]
     */
    protected $allWizards = [];

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
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param WizardInterface $wizard
     */
    public function addWizard(WizardInterface $wizard)
    {
        $this->allWizards[\get_class($wizard)] = $wizard;
        $this->configurationIsSynced = false;
    }

    public function getWizard($class)
    {
        if (!\array_key_exists($class, $this->allWizards)) {
            throw new \Exception(sprintf('Missing wizard: `%s`', $class));
        }

        return $this->allWizards[$class];
    }

    public function syncConfiguration()
    {
        if (!$this->configurationIsSynced) {
            foreach ($this->allWizards as $installedWizard) {
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
                foreach ($this->allWizards as $wizard) {
                    if ($configurationItem->getClass() == \get_class($wizard)) {
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
     * @return WizardInterface[]
     */
    public function getAllWizards()
    {
        return $this->allWizards;
    }

    /**
     * @return array|ConfigurationItem[]
     *
     * @codeCoverageIgnore Alias
     */
    public function getAllAvailableWizardItems()
    {
        $this->syncConfiguration();

        return $this->configuration->getConfigurationList();
    }

    /**
     * @return Configuration
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return array|ConfigurationItem[]
     *
     * @codeCoverageIgnore Alias
     */
    public function getAllEnabledWizardItems()
    {
        $this->syncConfiguration();

        return $this->configuration->getAllEnabled();
    }

    /**
     * @param null $changeType
     *
     * @return ConfigurationItem[]|array
     *
     * @codeCoverageIgnore Alias
     */
    public function getConfigurationUnsavedChanges($changeType = null)
    {
        return $this->configuration->getChanges($changeType);
    }

    /**
     * @param $wizardOrClass
     *
     * @return bool
     *
     * @codeCoverageIgnore Alias
     */
    public function wizardIsNew($wizardOrClass)
    {
        return $this->wizardIs(Configuration::CHANGES_ADDED, $wizardOrClass);
    }

    /**
     * @param $wizardOrClass
     *
     * @return bool
     *
     * @codeCoverageIgnore Alias
     */
    public function wizardIsUpdated($wizardOrClass)
    {
        return $this->wizardIs(Configuration::CHANGES_UPDATED, $wizardOrClass);
    }

    /**
     * @param $wizardOrClass
     *
     * @return bool
     *
     * @codeCoverageIgnore Alias
     */
    public function wizardIsRemoved($wizardOrClass)
    {
        return $this->wizardIs(Configuration::CHANGES_REMOVED, $wizardOrClass);
    }

    /**
     * @param $changeType
     * @param $wizardOrClass
     *
     * @return bool
     */
    protected function wizardIs($changeType, $wizardOrClass)
    {
        $this->syncConfiguration();

        $class = $wizardOrClass;
        if (\is_object($wizardOrClass)) {
            if ($wizardOrClass instanceof ConfigurationItem) {
                $class = $wizardOrClass->getClass();
            } else {
                $class = \get_class($wizardOrClass);
            }
        }

        foreach ($this->configuration->getChanges($changeType) as $newConfigurationItem) {
            if ($newConfigurationItem->getClass() == $class) {
                return true;
            }
        }

        return false;
    }
}
