<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.28.
 * Time: 12:52
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\Configurable;

use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

class ConfigurableRecipe extends BaseRecipe
{
    public function getName()
    {
        return 'configurable';
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();
        $rootNode
            ->info('<comment>This is a test recipe.</comment>')
            ->children()
                ->scalarNode('name')
                    ->info('<comment>Set a name.</comment>')
                    ->cannotBeEmpty()
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
