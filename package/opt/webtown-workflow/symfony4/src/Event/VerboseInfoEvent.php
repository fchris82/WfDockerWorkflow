<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.03.27.
 * Time: 22:22
 */

namespace App\Event;

use Symfony\Component\EventDispatcher\Event;

class VerboseInfoEvent extends Event
{
    protected $info;

    /**
     * VerboseInfoEvent constructor.
     * @param $info
     */
    public function __construct($info)
    {
        $this->info = $info;
    }

    public function getInfo()
    {
        return $this->info;
    }
}
