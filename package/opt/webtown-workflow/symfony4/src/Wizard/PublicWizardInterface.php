<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 14:26.
 */

namespace App\Wizard;

/**
 * Interface PublicWizardInterface.
 *
 * Azon Wizard-ok, amiket szeretnénk publikussá tenni és nem mondjuk vmi rész feladatot látnak el, aminek önállóan
 * semmi értelme.
 */
interface PublicWizardInterface
{
    public function getName();

    public function getInfo();
}
