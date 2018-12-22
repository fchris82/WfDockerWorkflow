<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.13.
 * Time: 14:11
 */

namespace App\Webtown\WorkflowBundle\Wizard;

use App\Webtown\WorkflowBundle\Exception\ConfigurationItemNotFoundException;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

class Configuration implements ConfigurationInterface
{
    // The double self::CONFIG_ROOT_MASK is "normal". It is required to configuration, the root can't be array node, so we need an array node under the root too. We use the same name.
    const CONFIG_ROOT_MASK = 'wizards';

    const CONFIG_NAME = 'name';
    const CONFIG_ENABLED = 'enabled';
    const CONFIG_GROUP = 'group';
    const CONFIG_PRIORITY = 'priority';

    const CHANGES_ADDED = 'added';
    const CHANGES_UPDATED = 'updated';
    const CHANGES_REMOVED = 'removed';

    /**
     * @var string
     */
    protected $configurationFilePath;

    /**
     * @var array|ConfigurationItem[]
     */
    protected $configurationList;

    /**
     * @var array
     */
    protected $changes = [];

    /**
     * Configuration constructor.
     *
     * @param string $wizardUserConfigurationFile
     */
    public function __construct(string $wizardUserConfigurationFile)
    {
        $this->configurationFilePath = $wizardUserConfigurationFile;
    }

    /**
     * The double self::CONFIG_ROOT_MASK is "normal". It is required to configuration, the root can't be array node, so
     * we need an array node under the root too. We use the same name.
     *
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder(static::CONFIG_ROOT_MASK);
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode(self::CONFIG_ROOT_MASK)
                    ->useAttributeAsKey('class')
                    ->prototype('array')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode(static::CONFIG_NAME)
                                ->info('<comment>The wizard name.</comment>')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->booleanNode(static::CONFIG_ENABLED)
                                ->info('<comment>Wizard is enabled?</comment>')
                                ->defaultTrue()
                            ->end()
                            ->scalarNode(static::CONFIG_GROUP)
                                ->info('<comment>The wizard group.</comment>')
                                ->defaultValue('')
                            ->end()
                            ->integerNode(static::CONFIG_PRIORITY)
                                ->info('<comment>Wizard priority. Higher goes up, lower goes down.</comment>')
                                ->defaultValue(0)
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return array|ConfigurationItem[]
     */
    public function getAllEnabled()
    {
        return array_filter($this->getConfigurationList(), function (ConfigurationItem $item) {
            return $item->isEnabled();
        });
    }

    /**
     * @return array|ConfigurationItem[]
     */
    public function getConfigurationList()
    {
        if (!$this->configurationList) {
            $baseConfig = file_exists($this->configurationFilePath) ? Yaml::parseFile($this->configurationFilePath) : [];
            $processor = new Processor();
            // The double self::CONFIG_ROOT_MASK is "normal". It is required to configuration, the root can't be array node, so we need an array node under the root too. We use the same name.
            $fullConfig = $processor->processConfiguration($this, [self::CONFIG_ROOT_MASK => [self::CONFIG_ROOT_MASK => $baseConfig]]);

            $configurationList = [];
            // The double self::CONFIG_ROOT_MASK is "normal". It is required to configuration, the root can't be array node, so we need an array node under the root too. We use the same name.
            foreach ($fullConfig[self::CONFIG_ROOT_MASK] as $class => $config) {
                $configurationList[] = new ConfigurationItem(
                    $class,
                    $config[static::CONFIG_NAME],
                    $config[static::CONFIG_ENABLED],
                    $config[static::CONFIG_GROUP],
                    $config[static::CONFIG_PRIORITY]
                );
            }
            usort($configurationList, [static::class, 'sort']);
            $this->configurationList = $configurationList;
        }

        return $this->configurationList;
    }

    public function add(ConfigurationItem $item)
    {
        $this->configurationList[] = $item;
        $this->addChanges(static::CHANGES_ADDED, $item);

        return $this;
    }

    public function set(ConfigurationItem $item)
    {
        foreach ($this->getConfigurationList() as $n => $configurationItem) {
            if ($configurationItem->getClass() == $item->getClass()) {
                $this->configurationList[$n] = $item;
                $this->addChanges(static::CHANGES_UPDATED, $item);
            }
        }

        return $this;
    }

    public function get($class)
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }

        foreach ($this->getConfigurationList() as $configurationItem) {
            if ($configurationItem->getClass() == $class) {
                return $configurationItem;
            }
        }

        throw new ConfigurationItemNotFoundException($class);
    }

    public function has($class)
    {
        if (\is_object($class)) {
            $class = \get_class($class);
        }

        foreach ($this->getConfigurationList() as $configurationItem) {
            if ($configurationItem->getClass() == $class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string|object|ConfigurationItem $classOrItem
     */
    public function remove($classOrItem)
    {
        $class = $classOrItem;
        if (\is_object($classOrItem)) {
            if ($classOrItem instanceof ConfigurationItem) {
                $class = $classOrItem->getClass();
            } else {
                $class = \get_class($classOrItem);
            }
        }

        foreach ($this->getConfigurationList() as $n => $configurationItem) {
            if ($configurationItem->getClass() == $class) {
                unset($this->configurationList[$n]);
                $this->addChanges(static::CHANGES_REMOVED, $configurationItem);
            }
        }
    }

    protected function addChanges($changeType, ConfigurationItem $configurationItem)
    {
        foreach ($this->changes as $type => $items) {
            if (\is_array($items)) {
                foreach ($items as $n => $item) {
                    if ($item->getClass() == $configurationItem->getClass()) {
                        unset($this->changes[$type][$n]);
                    }
                }
            }
        }
        $this->changes[$changeType][] = $configurationItem;
        usort($this->configurationList, [static::class, 'sort']);
    }

    /**
     * @param null $changeType
     *
     * @return array|ConfigurationItem[]
     */
    public function getChanges($changeType = null)
    {
        if (null === $changeType) {
            return $this->changes;
        }

        if (!$this->hasChanges($changeType)) {
            return [];
        }

        return $this->changes[$changeType];
    }

    public function hasChanges($changeType = null)
    {
        if (null === $changeType) {
            return \count($this->changes) > 0;
        }

        return array_key_exists($changeType, $this->changes) && \count($this->changes[$changeType]) > 0;
    }

    public function saveConfigurationList()
    {
        $configs = [];
        foreach ($this->getConfigurationList() as $configurationItem) {
            $configs[$configurationItem->getClass()] = [
                static::CONFIG_NAME => $configurationItem->getName(),
                static::CONFIG_ENABLED => $configurationItem->isEnabled(),
                static::CONFIG_GROUP => $configurationItem->getGroup(),
                static::CONFIG_PRIORITY => $configurationItem->getPriority(),
            ];
        }

        $content = "# You can edit this file with the `wizard --config` command!\n\n" . Yaml::dump($configs, 3);
        file_put_contents($this->configurationFilePath, $content);
    }

    public static function sort(ConfigurationItem $a, ConfigurationItem $b)
    {
        if ($a->getGroup() == $b->getGroup()) {
            if ($a->getPriority() == $b->getPriority()) {
                return strnatcmp($a->getClass(), $b->getClass());
            }

            return $a->getPriority() > $b->getPriority() ? -1 : 1;
        }

        // Groups are difference, if something is empty it will be "later".
        switch ('') {
            case $a->getGroup():
                return 1;
            case $b->getGroup():
                return -1;
        }

        return strnatcmp($a->getGroup(), $b->getGroup());
    }

    public function getConfigurationFilePath()
    {
        return $this->configurationFilePath;
    }
}
