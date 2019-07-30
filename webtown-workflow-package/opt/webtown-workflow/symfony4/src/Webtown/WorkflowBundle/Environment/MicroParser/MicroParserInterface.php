<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.11.30.
 * Time: 12:35
 */

namespace App\Webtown\WorkflowBundle\Environment\MicroParser;

interface MicroParserInterface
{
    public function get($workingDirectory, $key, $default);

    public function has($workingDirectory, $key);
}
