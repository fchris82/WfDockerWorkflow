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
        return static::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>SMTP e-mail sender.</comment>')
        ;

        return $rootNode;
    }
}