<?php

declare(strict_types=1);
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.08.11.
 * Time: 15:54.
 */

namespace App\Webtown\WorkflowBundle\Wizard;

interface WizardInterface
{
    /**
     * @return string
     */
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
