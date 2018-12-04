<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 15:42
 */

namespace App\Extension;

use App\Event\Extension\InstallEvent;
use App\Event\ExtensionEvents;
use App\Exception\CommanderRunException;
use App\Exception\Extension\ExtensionIsInsalledException;
use App\Exception\Extension\InvalidSourceException;
use App\Exception\Extension\MissingSourceCacheFileException;
use App\Exception\Extension\MissingSourceTypeException;
use App\Exception\Extension\UnknownOrInvalidExtension;
use App\Exception\Extension\UnknownOrInvalidNamespace;
use App\Extension\Installer\InstallerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class ExtensionManager
{
    const SOURCE_TYPE = 0;
    const SOURCE_SOURCE = 1;

    const INSTALL_TMP_DIR = '/tmp/wf_extension';
    const SOURCE_TYPE_SEPARATOR = '://';

    const RECIPE_EXTENSION_NAMESPACE = 'App\\Recipes';
    const WIZARD_EXTENSION_NAMESPACE = 'App\\Wizards';

    const RECIPE_SUBDIRECTORY = 'recipes';
    const WIZARD_SUBDIRECTORY = 'wizards';

    const SOURCE_FILE_CACHE = '.source';

    /**
     * @var string
     */
    protected $hostConfigurationPath;

    /**
     * @var Filesystem
     */
    protected $fileSystem;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var InstallerInterface[]
     */
    protected $installers = [];

    /**
     * @var array
     */
    protected $installerPriorities = [];

    /**
     * ExtensionManager constructor.
     *
     * @param string                   $hostConfigurationPath
     * @param Filesystem               $fileSystem
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(string $hostConfigurationPath, Filesystem $fileSystem, EventDispatcherInterface $eventDispatcher)
    {
        $this->hostConfigurationPath = $hostConfigurationPath;
        $this->fileSystem = $fileSystem;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Register installer
     *
     * @param InstallerInterface $installer
     * @param int|null           $priority
     */
    public function addInstaller(InstallerInterface $installer, int $priority = null)
    {
        $this->installers[$installer->getName()] = $installer;
        $this->installerPriorities[$installer->getName()] = $priority ?: $installer::getPriority();
        sort($this->installerPriorities, SORT_NUMERIC | SORT_DESC);
    }

    /**
     * @param $fullSource
     * @param bool $forceUpdate
     *
     * @throws ExtensionIsInsalledException
     * @throws UnknownOrInvalidExtension
     * @throws UnknownOrInvalidNamespace
     * @throws CommanderRunException
     */
    public function installExtension($fullSource, $forceUpdate = false)
    {
        $installer = $this->getInstallerBySource($fullSource);

        $event = new InstallEvent($fullSource, $installer, static::INSTALL_TMP_DIR);
        $this->eventDispatcher->dispatch(ExtensionEvents::PRE_INSTALL_EVENT, $event);

        $installer->install($this->getCleanSource($fullSource), $event->getTargetPath());
        // Create source cache file
        $this->fileSystem->dumpFile(
            $event->getTargetPath() . \DIRECTORY_SEPARATOR . static::SOURCE_FILE_CACHE,
            $fullSource
        );

        $this->eventDispatcher->dispatch(ExtensionEvents::POST_INSTALL_EVENT, $event);

        $namespace = $this->getExtensionNamespace();
        $targetPath = $this->getTargetPathByNamespace($namespace);

        if ($this->fileSystem->exists($targetPath)) {
            if (!$forceUpdate) {
                throw new ExtensionIsInsalledException(sprintf('The `%s` namespace is used! You have to delete the existing extension OR you should update instead of install!'));
            }

            // @todo (Chris) Itt lehetne ellenőrizni, hogy van-e eltérés a két könyvtár között, mert update esetén jó lenne kiírni, ,hogy minél változott valójában a tartalom, és minél nem.
            $this->fileSystem->remove($targetPath);
        }

        $this->fileSystem->mirror(static::INSTALL_TMP_DIR, $targetPath);
        $this->fileSystem->remove(static::INSTALL_TMP_DIR);

        $event->setTargetPath($targetPath);
        $this->eventDispatcher->dispatch(ExtensionEvents::CLEANUP_INSTALL_EVENT, $event);
    }

    public function updateExtension($path)
    {
        $cacheFile = trim($path, \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR . static::SOURCE_FILE_CACHE;
        if (!$this->fileSystem->exists($cacheFile)) {
            throw new MissingSourceCacheFileException($path);
        }
        $fullSource = trim(file_get_contents($cacheFile));

        $this->installExtension($fullSource, true);
    }

    protected function getInstallerBySource($source)
    {
        $sourceType = $this->getSourceType($source);

        return $this->getInstaller($sourceType);
    }

    protected function getSourceType($fullSource)
    {
        return $this->parseSource($fullSource, static::SOURCE_TYPE);
    }

    protected function getCleanSource($fullSource)
    {
        return $this->parseSource($fullSource, static::SOURCE_SOURCE);
    }

    /**
     * Parse a source. See the self::SOURCE_* constants!
     *
     * @param string $fullSource
     * @param int    $n
     *
     * @throws MissingSourceTypeException
     *
     * @return mixed
     */
    protected function parseSource(string $fullSource, int $n)
    {
        $parts = explode(static::SOURCE_TYPE_SEPARATOR, $fullSource, 2);

        if (\count($parts) < 2) {
            throw new MissingSourceTypeException(sprintf(
                'Missing source type: `%s`. You have to set the source type wiht `%s`, eg: `%s%s...',
                $fullSource,
                static::SOURCE_TYPE_SEPARATOR,
                implode(static::SOURCE_TYPE_SEPARATOR . '...`, `', array_keys($this->installers)),
                static::SOURCE_TYPE_SEPARATOR
            ));
        }

        return $parts[$n];
    }

    /**
     * In the downloaded extension dir try to find a PHP file to read the extension namespace.
     *
     * @throws UnknownOrInvalidExtension
     *
     * @return string
     */
    protected function getExtensionNamespace(): string
    {
        $finder = Finder::create()
            ->in(static::INSTALL_TMP_DIR)
            ->files()
            ->name('*.php')
            ->depth(0);
        /** @var SplFileInfo $phpFile */
        foreach ($finder as $phpFile) {
            $namespace = $this->parseNamespace($phpFile->getPathname());
            if ($namespace) {
                return $namespace;
            }
        }

        if (!$namespace) {
            throw new UnknownOrInvalidExtension();
        }
    }

    /**
     * Read the PHP file namespace from file by file source.
     *
     * @param string $src
     *
     * @return null|string
     */
    protected function parseNamespace(string $src): ?string
    {
        $tokens = token_get_all($src);
        $count = \count($tokens);
        $i = 0;
        $namespace = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (\is_array($token) && T_NAMESPACE === $token[0]) {
                // Found namespace declaration
                while (++$i < $count) {
                    if (';' === $tokens[$i]) {
                        $namespace_ok = true;
                        $namespace = trim($namespace);
                        break;
                    }
                    $namespace .= \is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            ++$i;
        }
        if (!$namespace_ok) {
            return null;
        } else {
            return trim($namespace, '\\');
        }
    }

    /**
     * Get the path where we should install the extension by PHP namespace.
     *
     * @param string $namespace
     *
     * @throws UnknownOrInvalidNamespace
     *
     * @return string
     */
    protected function getTargetPathByNamespace(string $namespace): string
    {
        list($mainNamespace, $typeNamespace, $baseNamespace, $other) = explode('\\', $namespace, 4);
        if (!$other) {
            throw new UnknownOrInvalidNamespace('Invalid namespace: ' . $namespace);
        }
        switch ($mainNamespace . '\\' . $typeNamespace) {
            case static::RECIPE_EXTENSION_NAMESPACE:
                $subDirectory = static::RECIPE_SUBDIRECTORY;
                break;
            case static::WIZARD_EXTENSION_NAMESPACE:
                $subDirectory = static::WIZARD_SUBDIRECTORY;
                break;
            default:
                throw new UnknownOrInvalidNamespace('Unknown namespace: ' . $namespace);
        }

        return sprintf('%s/%s/%s', $this->hostConfigurationPath, $subDirectory, $baseNamespace);
    }

    /**
     * Get list of ordered installers.
     *
     * @return array
     */
    public function getInstallers()
    {
        $orderedInstallers = [];
        foreach (array_keys($this->installerPriorities) as $installerName) {
            $orderedInstallers[$installerName] = $this->installers[$installerName];
        }

        return $orderedInstallers;
    }

    /**
     * @param string $sourceType
     *
     * @throws InvalidSourceException
     *
     * @return InstallerInterface
     */
    public function getInstaller(string $sourceType): InstallerInterface
    {
        if (!array_key_exists($sourceType, $this->installers)) {
            throw new InvalidSourceException(sprintf(
                'Invalid source: `%s`. You have to start one of them: `%s%s...`',
                $sourceType,
                implode(static::SOURCE_TYPE_SEPARATOR . '...`, `', array_keys($this->installers)),
                static::SOURCE_TYPE_SEPARATOR
            ));
        }

        return $this->installers[$sourceType];
    }

    /**
     * @return array|string[]
     */
    public function getAllowedInstallerTypes(): array
    {
        return array_keys($this->installers);
    }

    /**
     * @return array|SplFileInfo[]
     */
    public function getInstalledExtensions(): array
    {
        $recipesPath = $this->hostConfigurationPath . \DIRECTORY_SEPARATOR . static::RECIPE_SUBDIRECTORY;
        $wizardsPath = $this->hostConfigurationPath . \DIRECTORY_SEPARATOR . static::WIZARD_SUBDIRECTORY;
        $finder = Finder::create()
            ->in([$recipesPath, $wizardsPath])
            ->directories()
            ->depth(0);
        $extensions = [];
        /** @var SplFileInfo $directory */
        foreach ($finder as $directory) {
            $extensions[] = $directory;
        }

        return $extensions;
    }
}
