<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\Base;

use AppBundle\Configuration\HiddenRecipe;
use AppBundle\Event\ConfigurationEvents;
use AppBundle\Event\DumpEvent;
use AppBundle\Event\FinishEvent;
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
    // @todo (Chris) Ezeket vhogy kívülről kellene betölteni, csak sajnos "elrejtjük" a receptek elől jelenleg. Esetleg eseménykezelővel is betölthetjük a `EventSubscriberInterface` segítségével, ha vhol váltunk olyan eseményt a "bejegyzése" UTÁN, hogy szétszórjuk az adatokat, vagy esetleg lehetne egy service amit elér.
    protected $targetDirectory = '.wf';

    protected $configFile = '.wf.yml';

    const NAME = 'base';

    public function getName()
    {
        return self::NAME;
    }

    /**
     * @param string $targetPath
     * @param array  $recipeConfig Here it is the `$globalConfig`
     * @param array  $globalConfig
     *
     * @return array
     *
     * @see \AppBundle\Configuration\Builder::build()
     */
    public function getTemplateVars($targetPath, $recipeConfig, $globalConfig)
    {
        return array_merge(parent::getTemplateVars($targetPath, $recipeConfig, $globalConfig), [
            'wf_target_directory' => $this->targetDirectory,
            'wf_config_file' => $this->configFile,
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
            ConfigurationEvents::BEFORE_DUMP => 'changeReadmeMdPath',
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
        if (strpos($path, '/' . self::NAME . '/README.md') > 0) {
            $dumpEvent->setTargetPath(str_replace('/' . self::NAME . '/README.md', '/README.md', $path));
        }
    }
}
