<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:23
 */

namespace App\Recipes\MailHog;

use App\Recipes\NginxReverseProxy\NginxReverseProxyRecipe;
use App\Webtown\WorkflowBundle\Configuration\Environment;
use App\Webtown\WorkflowBundle\Exception\SkipSkeletonFileException;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Recipe
 *
 * E-mail sender.
 */
class MailHogRecipe extends BaseRecipe
{
    const NAME = 'mailhog';

    /**
     * @var Environment
     */
    protected $environment;

    /**
     * Recipe constructor.
     *
     * @param \Twig_Environment        $twig
     * @param EventDispatcherInterface $eventDispatcher
     * @param Environment              $environment
     */
    public function __construct(\Twig_Environment $twig, EventDispatcherInterface $eventDispatcher, Environment $environment)
    {
        parent::__construct($twig, $eventDispatcher);
        $this->environment = $environment;
    }

    public function getName()
    {
        return static::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $defaultTld = trim(
            $this->environment->getConfigValue(Environment::CONFIG_DEFAULT_LOCAL_TLD, '.loc'),
            '.'
        );
        $defaultHost = sprintf(
            '%s.%s.%s',
            static::NAME,
            NginxReverseProxyRecipe::PROJECT_NAME_PARAMETER_NAME,
            $defaultTld
        );

        $rootNode
            ->info('<comment>MailHog e-mail catcher. You can use the ports 1025 and 80.</comment>')
            ->children()
                ->variableNode('nginx_reverse_proxy_host')
                    ->info('You can set a custom domain that you can use to allow the webpage. Set false if you don\'t want to use it.')
                    ->defaultValue($defaultHost)
                    ->example('mailhog.custom.loc')
                ->end()
            ->end()
        ;

        return $rootNode;
    }

    /**
     * {@inheritdoc}
     */
    protected function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        switch ($fileInfo->getFilename()) {
            case 'docker-compose.nginx-reverse-proxy.yml':
                if (!isset($config['nginx_reverse_proxy_host']) || !$config['nginx_reverse_proxy_host']) {
                    throw new SkipSkeletonFileException();
                }
                break;
        }

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
