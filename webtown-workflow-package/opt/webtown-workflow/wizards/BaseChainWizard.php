<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 11:27.
 */

namespace Wizards;

use App\Exception\WizardSomethingIsRequiredException;
use App\Wizard\WizardInterface;
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
            try {
                $wizard->checkRequires($targetProjectDirectory);
                if (!$wizard->isBuilt($targetProjectDirectory)) {
                    $stepTargetProjectDirectory = $wizard->runBuild($targetProjectDirectory);
                }
            } catch (WizardSomethingIsRequiredException $e) {
                $missingRequires = $e->getMessage();
            }
        }

        return $targetProjectDirectory;
    }

    public function isBuilt($targetProjectDirectory)
    {
        return false;
    }
}
