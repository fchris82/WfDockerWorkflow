<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace App\Wizard;

interface WizardInterface
{
    public function getDefaultName();

    public function getDefaultGroup();

    public function getInfo();

    public function isHidden();

    public function isBuilt($targetProjectDirectory);

    /**
     * @return string
     */
    public function runBuild($targetProjectDirectory);
}
