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
    public function getName();
    public function install($source, $target);
}
