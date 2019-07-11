<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.23.
 * Time: 12:18
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Recipes\SimpleSkipFile;

use App\Webtown\WorkflowBundle\Exception\SkipSkeletonFileException;
use App\Webtown\WorkflowBundle\Recipes\BaseRecipe;
use Symfony\Component\Finder\SplFileInfo;

class SimpleSkipFileRecipe extends BaseRecipe
{
    public function getName()
    {
        return 'simple_skip_file';
    }

    public function buildSkeletonFile(SplFileInfo $fileInfo, $config)
    {
        if ('skip.txt' == $fileInfo->getFilename()) {
            throw new SkipSkeletonFileException();
        }

        return parent::buildSkeletonFile($fileInfo, $config);
    }
}
