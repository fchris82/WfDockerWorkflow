<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:22
 */

namespace App\Webtown\WorkflowBundle\Event\Configuration;

use Symfony\Contracts\EventDispatcher\Event;

class VerboseInfoEvent extends Event
{
    /**
     * @var string
     */
    protected $info;

    /**
     * VerboseInfoEvent constructor.
     *
     * @param string $info
     *
     * @codeCoverageIgnore Simple setter
     */
    public function __construct($info)
    {
        $this->info = $info;
    }

    /**
     * @return string
     *
     * @codeCoverageIgnore Simple getter
     */
    public function getInfo()
    {
        return $this->info;
    }
}
