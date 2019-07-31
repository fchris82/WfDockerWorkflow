<?php declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.25.
 * Time: 16:10
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Configuration;

class Environment extends \App\Webtown\WorkflowBundle\Configuration\Environment
{
    public function setEnv($env): void
    {
        $this->env = $env;
    }
}
