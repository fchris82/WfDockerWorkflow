<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2019.01.28.
 * Time: 11:55
 */

namespace App\Webtown\WorkflowBundle\Tests\Dummy\Environment;


class IoManager extends \App\Webtown\WorkflowBundle\Environment\IoManager
{
    /**
     * @var array
     */
    protected $outputLog = [];

    public function writeln($text)
    {
        $this->outputLog[] = $text;
    }

    public function getLog()
    {
        return $this->outputLog;
    }

    public function getLogAsString()
    {
        return implode("\n", $this->outputLog);
    }
}
