<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 21:55
 */

namespace Recipes\NginxReverseProxy;

use Recipes\BaseRecipe;
use App\Configuration\Environment;
use App\Event\BuildInitEvent;
use App\Event\ConfigurationEvents;
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

    const SERVICE_NAME_PARAMETER_NAME = '%service%';
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
        return static::NAME;
    }

    /**
     * Create default host (only [project_name].[default_tld] for the FIRST service)
     *
     * @param $projectPath
     * @param $recipeConfig
     * @param $globalConfig
     *
     * @return \App\Skeleton\SkeletonFile[]|array
     *
     * @throws \Exception
     * @throws \ReflectionException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function build($projectPath, $recipeConfig, $globalConfig)
    {
        $recipeConfig = $this->setTheDefaultHostIfNotSet($projectPath, $recipeConfig, $globalConfig);

        return parent::build($projectPath, $recipeConfig, $globalConfig);
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
                                $defaultHost = sprintf(
                                    '%s.%s.%s',
                                    static::SERVICE_NAME_PARAMETER_NAME,
                                    static::PROJECT_NAME_PARAMETER_NAME,
                                    $defaultTld
                                );
                                $defaultPort = 80;

                                return [
                                    // If the project name: `project` --> `project.loc`
                                    'host' => is_array($v) && array_key_exists('host', $v) ? $v['host'] : $defaultHost,
                                    'port' => (int) (is_array($v) && array_key_exists('port', $v) ? $v['port'] : (!is_array($v) && $v ? $v : $defaultPort)),
                                ];
                            })
                            ->end()
                        ->end()
                        // Replace the service names in domains
                        ->validate()
                            ->always(function ($v) {
                                foreach ($v as $serviceName => $settings) {
                                    $settings['host'] = strtr($settings['host'], [static::SERVICE_NAME_PARAMETER_NAME => $serviceName]);
                                    $v[$serviceName] = $settings;
                                }

                                return $v;
                            })
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
        $buildInitEvent->setParameter(static::PROJECT_NAME_PARAMETER_NAME, $config['name']);
    }

    /**
     * Set a default host (only the [project_name].[tld] format) for the first service if there isn't set it anywhere.
     *
     * @param string $projectPath
     * @param array  $recipeConfig
     * @param array  $globalConfig
     *
     * @return array
     */
    protected function setTheDefaultHostIfNotSet($projectPath, $recipeConfig, $globalConfig)
    {
        $defaultTld = trim(
            $this->environment->getConfigValue(Environment::CONFIG_DEFAULT_LOCAL_TLD, '.loc'),
            '.'
        );
        $defaultHost = sprintf('%s.%s', $globalConfig['name'], $defaultTld);

        if (!$this->defaultHostIsSet($recipeConfig, $defaultHost)) {
            foreach ($recipeConfig['settings'] as $serviceName => $settings) {
                // Only the default host name exists: [service_name].[project_name].[tld]
                if (strpos($settings['host'], $serviceName) === 0) {
                    $settings['host'] = $defaultHost . ' ' . $settings['host'];
                    $recipeConfig['settings'][$serviceName] = $settings;
                }
                break;
            }
        }

        return $recipeConfig;
    }

    /**
     * It tries to find to project default host name (format: [project_name].[tld] ), and if it is set somewhere it will
     * return true.
     *
     * @param array  $recipeConfig
     * @param string $defaultHost
     *
     * @return bool
     */
    protected function defaultHostIsSet($recipeConfig, $defaultHost)
    {
        foreach ($recipeConfig['settings'] as $serviceName => $settings) {
            $hosts = explode(' ', $settings['host']);
            if (in_array($defaultHost, $hosts)) {
                return true;
            }
        }

        return false;
    }
}
