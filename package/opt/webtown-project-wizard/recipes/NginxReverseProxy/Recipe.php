<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\NginxReverseProxy;

use AppBundle\Configuration\BaseRecipe;
use AppBundle\Configuration\Environment;
use AppBundle\Event\BuildInitEvent;
use AppBundle\Event\ConfigurationEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class Recipe
 *
 * Allow nginx-reverse-proxy config.
 *
 * @package Recipes\NginxReverseProxy
 */
class Recipe extends BaseRecipe implements EventSubscriberInterface
{
    const NAME = 'nginx_reverse_proxy';

    // We try to give a lazy solution with default host settings
    const PROJECT_NAME_PARAMETER_NAME = '%config.name%';

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
        return self::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>You can enable the nginx-reverse-proxy.</comment>')
            ->children()
                ->scalarNode('network_name')
                    ->info('<comment>The nginx-reverse-proxy network name.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('reverse-proxy')
                ->end()
                ->arrayNode('settings')
                    ->info('<comment>You have to set the service and its <info>host</info> and <info>port</info> settings.</comment>')
                    ->useAttributeAsKey('service')
                    ->variablePrototype()
                        ->beforeNormalization()
                            ->always(function ($v) {
                                $defaultTld = trim(
                                    $this->environment->getConfigValue(Environment::CONFIG_DEFAULT_LOCAL_TLD, '.loc'),
                                    '.'
                                );
                                $defaultHost = self::PROJECT_NAME_PARAMETER_NAME . '.' . $defaultTld;
                                $defaultPort = 80;

                                return [
                                    // If the project name: `project` --> `project.loc`
                                    'host' => is_array($v) && array_key_exists('host', $v) ? $v['host'] : $defaultHost,
                                    'port' => (int) (is_array($v) && array_key_exists('port', $v) ? $v['port'] : (!is_array($v) && $v ? $v : $defaultPort)),
                                ];
                            })
                            ->end()
                        ->end()
                        ->example([
                            'service1' => '~',
                            'service2' => ['host' => 'phpmyadmin.project.loc', 'port' => 81],
                            'service3' => 82,
                        ])
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * We need to the project name from the config. We put a placeholder to `host` values, and we will have to change it!
     *
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
            ConfigurationEvents::BUILD_INIT => 'findProjectName',
        ];
    }

    public function findProjectName(BuildInitEvent $buildInitEvent)
    {
        $config = $buildInitEvent->getConfig();
        $buildInitEvent->setParameter(self::PROJECT_NAME_PARAMETER_NAME, $config['name']);
    }
}
