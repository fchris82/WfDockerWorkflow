<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 11:27.
 */

namespace App\Wizard;

use App\Wizard\Helper\ComposerInstaller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BaseChainWizard.
 *
 * Több Wizardot lehet egymás után fűzni benne. Pl:
 *
 * <code>
 *      protected function getWizardNames()
 *      {
 *          return [
 *              # Projekt létrehozása
 *              SymfonyBuildWizard::class,
 *              PhpMdSkeleton::class,
 *              PhpCsFixSkeleton::class,
 *              # Az eddig összegyűlt composer require-eket telepítjük.
 *              new ComposerInstallForChain($this),
 *              # Commitolunk egyet
 *              new GitCommitWizardForChain('Add PHPMD and PHP-CS-FIXER'),
 *              $this->dockerWizard,
 *              GitlabCISkeleton::class,
 *          ];
 *      }
 * </code>
 */
abstract class BaseChainWizard extends BaseWizard implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $composerPackages = [];

    abstract protected function getWizardNames();

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return array|WizardInterface[]
     */
    protected function getWizards()
    {
        $wizards = [];
        /** @var WizardInterface $wizard */
        foreach ($this->getWizardNames() as $wizard) {
            $wizard = is_string($wizard)
                ? $this->container->get($wizard)
                : $wizard;
            $wizard
                ->setInput($this->input)
                ->setOutput($this->output)
                ->setCommand($this->command);
            $wizards[] = $wizard;
        }

        return $wizards;
    }

    public function build($targetProjectDirectory)
    {
        foreach ($this->getWizards() as $wizard) {
            if (!$wizard->isBuilt($targetProjectDirectory)) {
                $targetProjectDirectory = $wizard->build($targetProjectDirectory);
                if (!$targetProjectDirectory) {
                    var_dump(get_class($wizard));

                    $trace = debug_backtrace();
                    $simple = [];
                    foreach ($trace as $step) {
                        unset($step['object']);
                        $simple[] = $step;
                    }
                    file_put_contents(__DIR__ . '/../../../log1.txt', print_r(array_slice($simple, 0, 5), true));
                }
                // Kigyűjtjük, hogy milyen composer csomagokat kell telepíteni. A `ComposerInstallForChain` osztállyal lehet futtatni
                $this->composerPackages = $this->deepArrayMerge($this->composerPackages, $wizard->getRequireComposerPackages());
            }
        }

        return $targetProjectDirectory;
    }

    public function installComposerPackages($targetProjectDirectory)
    {
        $composerPackages = $this->getRequireComposerPackages();
        ComposerInstaller::installComposerPackages($targetProjectDirectory, $composerPackages, $this->output);

        // Reset!
        $this->composerPackages = [];
    }

    /**
     * 'dev' => [... dev packages ...]
     * 'nodev' => [... nodev packages ...].
     *
     * Eg:
     * <code>
     *  return ['dev' => ["friendsofphp/php-cs-fixer:~2.3.3"]];
     * </code>
     *
     * @return array
     */
    public function getRequireComposerPackages()
    {
        return $this->composerPackages;
    }

    protected function deepArrayMerge($array1, $array2)
    {
        foreach ($array2 as $key => $value) {
            if (is_int($key)) {
                return array_merge($array1, $array2);
            } elseif (!array_key_exists($key, $array1)) {
                $array1[$key] = $value;
            } elseif (is_array($value)) {
                $array1[$key] = $this->deepArrayMerge($array1[$key], $value);
            } else {
                $array1[$key] = $value;
            }
        }

        return $array1;
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }
}
