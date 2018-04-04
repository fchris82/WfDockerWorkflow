<?php
/**
 * Created by IntelliJ IDEA.
 * User: chris
 * Date: 2017.09.08.
 * Time: 14:13.
 */

namespace App\Wizard;

/**
 * Class ManagerItem.
 *
 * Ezt csak arra használjuk, hogy a services.yml-ből kigyűjtsük a wizard-okat, és majd később rendezni tudjuk illetve
 * csoportosítani a megadott `group` alapján.
 */
class ManagerItem
{
    /**
     * @var string
     */
    protected $group;

    /**
     * @var PublicWizardInterface|WizardInterface
     */
    protected $wizard;

    /**
     * ManagerItem constructor.
     *
     * @param string                                $group
     * @param PublicWizardInterface|WizardInterface $wizard
     */
    public function __construct($group, PublicWizardInterface $wizard)
    {
        $this->group = $group;
        $this->wizard = $wizard;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return PublicWizardInterface|WizardInterface
     */
    public function getWizard()
    {
        return $this->wizard;
    }
}
