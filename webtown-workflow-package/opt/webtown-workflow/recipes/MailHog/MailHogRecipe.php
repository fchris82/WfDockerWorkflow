<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.14.
 * Time: 22:23
 */

namespace Recipes\MailHog;

use Recipes\BaseRecipe;

/**
 * Class Recipe
 *
 * E-mail sender.
 */
class MailHogRecipe extends BaseRecipe
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
