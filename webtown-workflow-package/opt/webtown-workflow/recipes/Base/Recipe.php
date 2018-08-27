<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\Base;

use App\Configuration\Environment;
use App\Event\FinishEvent;
use Recipes\HiddenRecipe;
use App\Event\ConfigurationEvents;
use App\Event\DumpEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Recipe
 *
 * The BASE.
 *
 * @package Recipes\Base
 */
class Recipe extends HiddenRecipe implements EventSubscriberInterface
{
    const NAME = 'base';

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * Recipe constructor.
     *
     * @param \Twig_Environment $twig
     * @param Environment $environment
     */
    public function __construct(\Twig_Environment $twig, Environment $environment)
    {
        parent::__construct($twig);
        $this->environment = $environment;
    }

    public function getName()
    {
        return static::NAME;
    }

    /**
     * @param string $targetPath
     * @param array  $recipeConfig Here it is the `$globalConfig`
     * @param array  $globalConfig
     *
     * @return array
     *
     * @see \App\Configuration\Builder::build()
     */
    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        return array_merge(parent::getTemplateVars($targetPath, $recipeConfig, $globalConfig), [
            'wf_target_directory' => $this->environment->getConfigValue(Environment::CONFIG_WORKING_DIRECTORY),
            'wf_config_file' => $this->environment->getConfigValue(Environment::CONFIG_CONFIGURATION_FILE),
            'wf_env_file' => $this->environment->getConfigValue(Environment::CONFIG_ENV_FILE),
        ]);
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            ConfigurationEvents::BEFORE_DUMP => [
                ['changeReadmeMdPath'],
            ],
        ];
    }

    /**
     * We want to move the README.md to target root!
     *
     * @param DumpEvent $dumpEvent
     */
    public function changeReadmeMdPath(DumpEvent $dumpEvent)
    {
        $path = $dumpEvent->getTargetPath();
        if (strpos($path, '/' . static::NAME . '/README.md') > 0) {
            $dumpEvent->setTargetPath(str_replace('/' . static::NAME . '/README.md', '/README.md', $path));
        }
    }
}
