<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:23
 */

namespace Recipes\Mail;

use AppBundle\Configuration\BaseRecipe;

/**
 * Class Recipe
 *
 * E-mail sender.
 *
 * @todo (Chris) A MailHog-ból is kellene egy verzió
 *
 * @package Recipes\Mail
 */
class Recipe extends BaseRecipe
{
    const NAME = 'mail';

    public function getName()
    {
        return self::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>SMTP e-mail sender.</comment>')
            ->children()
                ->scalarNode('image')
                    ->info('<comment>You can change the image.</comment>')
                    ->cannotBeEmpty()
                    ->defaultValue('tianon/exim4:latest')
                ->end()
            ->end()
        ;

        return $rootNode;
    }
}
