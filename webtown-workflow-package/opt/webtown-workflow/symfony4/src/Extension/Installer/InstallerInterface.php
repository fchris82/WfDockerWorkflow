<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2018.12.02.
 * Time: 15:51
 */

namespace App\Extension\Installer;


interface InstallerInterface
{
    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $source
     * @param string $target
     * @return void
     */
    public function install(string $source, string $target);

    /**
     * @return integer
     */
    public static function getPriority(): int;
}
