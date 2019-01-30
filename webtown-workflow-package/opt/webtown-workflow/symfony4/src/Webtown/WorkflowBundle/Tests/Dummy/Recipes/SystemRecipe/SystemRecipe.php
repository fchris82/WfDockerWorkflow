<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.22.
 * Time: 13:47
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SystemRecipe;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SystemRecipe extends \App\Webtown\WorkflowBundle\Recipes\SystemRecipe
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var NodeDefinition
     */
    protected $configuration;

    /**
     * SystemRecipe constructor.
     *
     * @param string                   $name
     * @param NodeDefinition           $configuration
     * @param \Twig_Environment        $twig
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct($name, $configuration, \Twig_Environment $twig, EventDispatcherInterface $eventDispatcher)
    {
        $this->name = $name;
        $this->configuration = $configuration;
        parent::__construct($twig, $eventDispatcher);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getConfig()
    {
        return $this->configuration;
    }
}
