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
use Symfony\Component\Console\Helper\ProcessHelper;
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
     * @var ProcessHelper
     */
    protected $processHelper;

    /**
     * Recipe constructor.
     *
     * @param \Twig_Environment $twig
     * @param Environment $environment
     */
    public function __construct(\Twig_Environment $twig, Environment $environment, ProcessHelper $processHelper)
    {
        parent::__construct($twig);
        $this->environment = $environment;
        $this->processHelper = $processHelper;
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
            'wf_list' => $this->environment->getConfigValue(Environment::CONFIG_CONFIGURATION_FILE),
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
                ['changeAutocompletePath'],
            ],
            ConfigurationEvents::FINISH => [
                ['fillAutocomplete'],
            ]
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

    /**
     * We want to move the README.md to target root!
     *
     * @param DumpEvent $dumpEvent
     */
    public function changeAutocompletePath(DumpEvent $dumpEvent)
    {
        // @todo (Chris) A fillAutocomplete-tel van értelme. Addig csak zavarna, hogy üres.
//        $path = $dumpEvent->getTargetPath();
//        if (strpos($path, '/' . static::NAME . '/autocomplete') > 0) {
//            $dumpEvent->setTargetPath(str_replace('/' . static::NAME . '/autocomplete', '/autocomplete', $path));
//        }
    }

    public function fillAutocomplete(FinishEvent $finishEvent)
    {
        // @todo (Chris) Itt futtatni kellene a wf list parancsot, hogy létrejöjjön az autocomplete fájl!
    }
}
