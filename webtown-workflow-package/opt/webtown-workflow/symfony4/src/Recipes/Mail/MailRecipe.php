<?php declare(strict_types=1);

namespace App\Recipes\Mail;

use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;

/**
 * Class Recipe
 *
 * E-mail sender.
 */
class MailRecipe extends BaseRecipe
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
