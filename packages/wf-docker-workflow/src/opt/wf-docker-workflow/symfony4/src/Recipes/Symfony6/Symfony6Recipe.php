<?php declare(strict_types=1);

namespace App\Recipes\Symfony6;

use App\Recipes\Symfony\AbstractSymfonyRecipe;

/**
 * Class Recipe
 *
 * Symfony 6 friendly environment
 */
class Symfony6Recipe extends AbstractSymfonyRecipe
{
    const NAME = 'symfony6';
    const SF_CONSOLE_COMMAND = 'bin/console';
    const SF_BIN_DIR = 'vendor/bin';
    const DEFAULT_VERSION = 'php8.0';
    const DEFAULT_INDEX_PHP = 'index.php';
    const DEFAULT_NGINX_ROOT = '%wf.project_path%/public';

    public static function getSkeletonParents(): array
    {
        return [AbstractSymfonyRecipe::class];
    }
}
