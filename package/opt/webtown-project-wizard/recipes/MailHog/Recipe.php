<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:23
 */

namespace Recipes\MailHog;

use AppBundle\Configuration\BaseRecipe;

/**
 * Class Recipe
 *
 * E-mail sender.
 *
 * @package Recipes\Mail
 */
class Recipe extends BaseRecipe
{
    const NAME = 'mailhog';

    public function getName()
    {
        return static::NAME;
    }

    public function getConfig()
    {
        $rootNode = parent::getConfig();

        $rootNode
            ->info('<comment>MailHog e-mail catcher. You can use the ports 25 and 80.</comment>')
        ;

        return $rootNode;
    }
}
